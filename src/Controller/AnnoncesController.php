<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Repository\AnnoncesRepository;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AnnoncesController extends AbstractController
{

    //Récupérer toutes les annonces
    #[Route('/api/annonces', name: 'liste_annonces', methods: ['GET'])]
    public function getListeAnnonces(AnnoncesRepository $annoncesRepository, SerializerInterface $serializer): JsonResponse
    {
        $annoncesListe = $annoncesRepository->findAll();
        $jsonAnnoncesListe = $serializer->serialize($annoncesListe, 'json', ['groups' => 'getAnnonces']);
        return new JsonResponse($jsonAnnoncesListe, Response::HTTP_OK, [], true);
    }

    //Récupérer une annonce en particulier
    #[Route('/api/annonces/{id}', name: 'detail_annonce', methods: ['GET'])]
    public function getDetailAnnonce(int $id,AnnoncesRepository $annoncesRepository, SerializerInterface $serializer): JsonResponse
    {
        $annonce = $annoncesRepository->find($id);
        if ($annonce) {
            $jsonAnnonce = $serializer->serialize($annonce, 'json', ['groups' => 'getAnnonces']);
            return new JsonResponse($jsonAnnonce, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null,Response::HTTP_NOT_FOUND);
    }

    //Supprimer une annonce
    #[Route('/api/annonces/{id}', name: 'supprimer_annonce', methods: ['DELETE'])]
    public function deleteAnnonce(Annonces $annonce, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($annonce);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    //Créer une annonce
    #[Route('/api/annonces', name:"creer_annonce", methods: ['POST'])]
    public function createAnnonce(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, CategoriesRepository $categoriesRepository, ValidatorInterface $validator): JsonResponse 
    {

        $annonce = $serializer->deserialize($request->getContent(), Annonces::class, 'json');
        $errors = $validator->validate($annonce);
        
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        
        $idCategorie = $content['idCategorie'] ?? -1;

        if ($idCategorie != 1){
            $annonce->setModele(null);
            $annonce->setMarque(null);
        }
        $annonce->setCategories($categoriesRepository->find($idCategorie)); 
        $em->persist($annonce);
        $em->flush();
        $jsonAnnonce = $serializer->serialize($annonce, 'json', ['groups' => 'getAnnonces']);
        $location = $urlGenerator->generate('detail_annonce', ['id' => $annonce->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAnnonce, Response::HTTP_CREATED, ["Location" => $location], true);
   }

   //Modifier une annonce
   #[Route('/api/annonces/{id}', name:"modifier_annonce", methods:['PUT'])]

    public function updateAnnonce(Request $request, SerializerInterface $serializer, Annonces $annonceActuelle, EntityManagerInterface $em, CategoriesRepository $categoriesRepository): JsonResponse 
    {
        $updatedAnnonce = $serializer->deserialize($request->getContent(), 
                Annonces::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $annonceActuelle]);
        $content = $request->toArray();
        $idCategorie = $content['idCategorie'] ?? -1;
        $updatedAnnonce->setCategories($categoriesRepository->find($idCategorie));
        
        $em->persist($updatedAnnonce);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }

   //Fonction permettant de trouver la marque grâce au modèle
    private function findMarqueByModele(string $modele): ?string
    {
        $marquesModeles = [
            'Audi' => [
                'Cabriolet', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8', 'R8', 'Rs3', 'Rs4', 'Rs5', 'Rs7',
                'S3', 'S4', 'S4 Avant', 'S4 Cabriolet', 'S5', 'S7', 'S8', 'SQ5', 'SQ7',
                'Tt', 'Tts', 'V8'
            ],
            'BMW' => [
                'M3', 'M4', 'M5', 'M535', 'M6', 'M635', 'Serie 1', 'Serie 2', 'Serie 3',
                'Serie 4', 'Serie 5', 'Serie 6', 'Serie 7', 'Serie 8'
            ],
            'Citroen' => [
                'C1', 'C15', 'C2', 'C25', 'C25D', 'C25E', 'C25TD', 'C3', 'C3 Aircross',
                'C3 Picasso', 'C4', 'C4 Picasso', 'C5', 'C6', 'C8', 'Ds3', 'Ds4', 'Ds5'
            ],
        ];

        foreach ($marquesModeles as $marque => $modeles) {
            foreach ($modeles as $modeleExist) {
                similar_text(strtolower($modele), strtolower($modeleExist), $similarity);

                if ($similarity >= 80) {
                    return $marque;
                }
            }
        }
        return null;
    }
}