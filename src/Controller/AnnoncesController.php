<?php

namespace App\Controller;

use App\Entity\Annonces;
use App\Entity\Categories;
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

        if ($idCategorie != 3){
            $annonce->setModele(null);
            $annonce->setMarque(null);
        } else {
            if (isset($content['modele'])) {
                $modele = $content['modele'];
                $testMarque = $this->findMarqueByModele($modele);
                if ($testMarque === null) {
                    return new JsonResponse('Le modèle renseigné est introuvable dans la liste des modèles autorisés', JsonResponse::HTTP_BAD_REQUEST, [], true);
                } else {
                    $marque = $testMarque[0];
                    $modeleExiste = $testMarque[1];
                    $annonce->setMarque($marque);
                    $annonce->setModele($modeleExiste);
                }
            } else {
                    return new JsonResponse('Un modèle de voiture doit absolument être renseigné pour la catégorie automobile', JsonResponse::HTTP_BAD_REQUEST, [], true);
            }
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
        if ($idCategorie != 3){
            $updatedAnnonce->setModele(null);
            $updatedAnnonce->setMarque(null);
        } else {
            if (isset($content['modele'])) {
                $modele = $content['modele'];
                $testMarque = $this->findMarqueByModele($modele);
                if ($testMarque === null) {
                    return new JsonResponse('Le modèle renseigné est introuvable dans la liste des modèles autorisés', JsonResponse::HTTP_BAD_REQUEST, [], true);
                } else {
                    $marque = $testMarque[0];
                    $modeleExiste = $testMarque[1];
                    $updatedAnnonce->setMarque($marque);
                    $updatedAnnonce->setModele($modeleExiste);
                } 
            } else {
                    return new JsonResponse('Un modèle de voiture doit absolument être renseigné pour la catégorie voiture', JsonResponse::HTTP_BAD_REQUEST, [], true);
            }
        }
        $em->persist($updatedAnnonce);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }

    //Fonction permettant de trouver la marque grâce au modèle
    private function findMarqueByModele(string $modele)
    {
        // Convertir le texte en minuscules, supprimer les espaces en trop et remplacer les caractères spéciaux
        $cherche  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ',' ');
	    $remplace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y','');
        $texte = strtolower(str_replace($cherche,$remplace, $modele));

        // Définir la liste des marques et modèles de véhicules
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
        
        // Parcourir la liste des marques et modèles
        $correspondances = array();
        foreach ($marquesModeles as $marque => $modeles) {
            foreach ($modeles as $modeleExist) {

                // Formater le modèle en enlevant les espaces, convertir en minuscules et remplacer les caractères spéciaux
                $modeleFormatte = strtolower(str_replace($cherche, $remplace, $modeleExist));

                // Vérifier si le modèle formaté est présent dans le texte saisi
                if (strpos($texte, $modeleFormatte) !== false) {
                    similar_text(strtolower($texte), strtolower($modeleExist), $similarity);
                    array_push($correspondances, [$modeleExist, $marque, $similarity]);
                }
            }
        }
        if (empty($correspondances)) {
            return null;
        } else {
            $valeurMax = -INF;
            $bestCorrespondance = array();

            foreach ($correspondances as $correspondance) {
                if ($correspondance[2] > $valeurMax) {
                    $valeurMax = $correspondance[2];
                    $bestCorrespondance = array($correspondance);  // Réinitialiser le tableau avec la correspondance ayant la valeur maximale
                }
            }        
            $bonneMarque = $bestCorrespondance[0][1];
            $bonModele = $bestCorrespondance[0][0];
            return [$bonneMarque, $bonModele];
        }     
    }

    //Créer une categorie
    #[Route('/api/categories', name:"creer_categorie", methods: ['POST'])]
    public function createCategorie(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, Categories $categories): JsonResponse 
    {

        $categorie = $serializer->deserialize($request->getContent(), Categories::class, 'json');
        $errors = $validator->validate($categorie);
        
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        
        
        $nomCategorie = $content['nom'];
        $categorie->setNom($nomCategorie);
        $em->persist($categorie);
        $em->flush();
        //$jsonCategorie = $serializer->serialize($categorie, 'json', ['groups' => 'getAnnonces']);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
   //Récupérer toutes les categories
   #[Route('/api/categories', name: 'liste_categories', methods: ['GET'])]
   public function getListeCategories(CategoriesRepository $categoriesRepository, SerializerInterface $serializer): JsonResponse
   {
       $categoriesListe = $categoriesRepository->findAll();
       $jsonCategoriesListe = $serializer->serialize($categoriesListe, 'json', ['groups' => 'getAnnonces']);
       return new JsonResponse($jsonCategoriesListe, Response::HTTP_OK, [], true);
   }

}