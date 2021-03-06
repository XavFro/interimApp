<?php

namespace App\Entity;

use App\Enum\ContractStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContractRepository")
 */
class Contract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     * @ORM\Column(type="datetime")
     */
    private $dateStart;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     * @ORM\Column(type="datetime")
     */
    private $dateEnd;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, columnDefinition="enum('waiting', 'progress', 'finished')")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Interim", inversedBy="contract")
     * @ORM\JoinColumn(nullable=false)
     */
    private $interim;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Mission", mappedBy="contract", cascade={"persist", "remove"})
     */
    private $mission;

    function __toString() {
        setlocale (LC_TIME, 'fr_FR.utf8','fra');
        $frenchDateStart = strftime("%d %B %Y", strtotime($this->dateStart->format("d-M-Y")));
        $frenchDateEnd = strftime("%d %B %Y", strtotime($this->dateEnd->format("d-M-Y")));
        return sprintf("%s %s / %s - %s", $this->getInterim()->getName(), $this->getInterim()->getSurname(), $frenchDateStart, $frenchDateEnd);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        if($dateEnd < $this->dateStart) {
            throw new \InvalidArgumentException("La date de fin ne peut être inférieur à la date de début");
        }

        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, ContractStatusEnum::getAvailableStatus())) {
            throw new \InvalidArgumentException("Statut invalide");
        }

        $this->status = $status;

        return $this;
    }

    public function getInterim(): ?Interim
    {
        return $this->interim;
    }

    public function setInterim(?Interim $interim): self
    {
        $this->interim = $interim;

        return $this;
    }

    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(Mission $mission): self
    {
        $this->mission = $mission;

        // set the owning side of the relation if necessary
        if ($this !== $mission->getContract()) {
            $mission->setContract($this);
        }

        return $this;
    }
}
