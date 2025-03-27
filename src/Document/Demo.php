<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class Demo
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: "string")]
    private string $title = "Test";
}

