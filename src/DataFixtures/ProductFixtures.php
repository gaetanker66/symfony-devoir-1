<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    private const NB_PRODUCTS = 20;

    public function load(ObjectManager $manager): void
    {

        for ($i = 1; $i <= self::NB_PRODUCTS; $i++) {
            $product = new Product();
            $product->setName("Produit $i")->setPrice(mt_rand(1, 999))->setQuantity(mt_rand(0, 10))->setDescription("description produit $i")->setImage('image.jpg');
            $manager->persist($product);
        }
        $manager->flush();
    }
}
