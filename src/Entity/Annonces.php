<?php

namespace App\Entity;

use App\Repository\AnnoncesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnnoncesRepository::class)]
class Annonces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getAnnonces"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getAnnonces"])]
    #[Assert\NotBlank(message: "Le titre de l'annonce est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le titre doit faire au moins {{ limit }} caractères", maxMessage: "Le titre ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["getAnnonces"])]
    #[Assert\NotBlank(message: "Le contenu de l'annonce est obligatoire")]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getAnnonces"])]
    private ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getAnnonces"])]
    private ?string $modele = null;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    #[Groups(["getAnnonces"])]
    private ?Categories $categories = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(?string $modele): self
    {
        $this->modele = $modele;

        return $this;
    }

    public function getCategories(): ?Categories
    {
        return $this->categories;
    }

    public function setCategories(?Categories $categories): self
    {
        $this->categories = $categories;

        return $this;
    }
}
