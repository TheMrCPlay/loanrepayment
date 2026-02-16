<?php

namespace App\Domain\Entity;

class Customer
{
    //  {
    //        "id": "c539792e-7773-4a39-9cf6-f273b2581438",
    //        "firstname": "Pupa",
    //        "lastname": "Lupa",
    //        "ssn": "0987654321",
    //        "email": "pupa.lupa@example.com"
    //    },

    public function __construct(
        private readonly string $id,
        private readonly string $firstname,
        private readonly string $lastname,
        private readonly string $ssn,
        private readonly string $email
    )
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getSsn(): string
    {
        return $this->ssn;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
