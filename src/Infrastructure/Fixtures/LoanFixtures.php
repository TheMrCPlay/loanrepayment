<?php

namespace App\Infrastructure\Fixtures;

use App\Infrastructure\Entity\LoanEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class LoanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $loans = [
            [
                'id' => '51ed9314-955c-4014-8be2-b0e2b13588a5',
                'customerId' => 'c539792e-7773-4a39-9cf6-f273b2581438',
                'reference' => 'LN12345678',
                'state' => 'ACTIVE',
                'amountIssued' => '100.00',
                'amountToPay' => '120.00',
            ],
            [
                'id' => 'a54b0796-2fcb-4547-b23d-125786600ec3',
                'customerId' => 'c539792e-7773-4a39-9cf6-f273b2581438',
                'reference' => 'LN22345678',
                'state' => 'ACTIVE',
                'amountIssued' => '200.00',
                'amountToPay' => '250.00',
            ],
            [
                'id' => 'f7f81281-64a9-47a7-af60-5c6896896d1f',
                'customerId' => 'd275ce5e-91c8-49fe-9407-1700b59efe80',
                'reference' => 'LN55522533',
                'state' => 'ACTIVE',
                'amountIssued' => '50.00',
                'amountToPay' => '70.00',
            ],
            [
                'id' => 'b8d26e7b-1607-441d-8bb0-87517a874572',
                'customerId' => 'c5c05eeb-ff02-4de6-b92e-a1b7f02320df',
                'reference' => 'LN20221212',
                'state' => 'ACTIVE',
                'amountIssued' => '66.00',
                'amountToPay' => '100.00',
            ],
        ];

        foreach ($loans as $loanData) {
            $loan = new LoanEntity();
            $loan->setId($loanData['id'])
                ->setCustomerId($loanData['customerId'])
                ->setReference($loanData['reference'])
                ->setState($loanData['state'])
                ->setAmountIssued($loanData['amountIssued'])
                ->setAmountToPay($loanData['amountToPay'])
                ->setPaidAmount('0.00');

            $manager->persist($loan);
        }

        $manager->flush();
    }
}