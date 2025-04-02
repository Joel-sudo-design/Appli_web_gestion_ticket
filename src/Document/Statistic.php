<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class Statistic
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $category = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }
}
