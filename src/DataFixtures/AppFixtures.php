<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create categories
        $clothing = new Category();
        $clothing->setName('Clothing');
        $clothing->setDescription('Discover our latest collection of trendy clothing');
        $clothing->setIcon('fa-tshirt');
        $manager->persist($clothing);

        $footwear = new Category();
        $footwear->setName('Footwear');
        $footwear->setDescription('Find the perfect shoes for any occasion');
        $footwear->setIcon('fa-shoe-prints');
        $manager->persist($footwear);

        $accessories = new Category();
        $accessories->setName('Accessories');
        $accessories->setDescription('Complete your look with our accessories');
        $accessories->setIcon('fa-glasses');
        $manager->persist($accessories);

        // Create products
        $products = [
            [
                'name' => 'Classic Jeans',
                'description' => 'Classic denim jeans for a casual look',
                'price' => 59.99,
                'image' => 'jeans.jpg',
                'stock' => 80,
                'category' => $clothing,
                'size' => '32',
                'color' => 'Blue'
            ],
            [
                'name' => 'Running Shoes',
                'description' => 'Perfect for your daily jog or workout session',
                'price' => 89.99,
                'image' => 'runing_shoes.jpg',
                'stock' => 50,
                'category' => $footwear,
                'size' => '42',
                'color' => 'Black'
            ],
            [
                'name' => 'Stylish Sunglasses',
                'description' => 'Protect your eyes in style',
                'price' => 49.99,
                'image' => 'sunglasses.jpg',
                'stock' => 75,
                'category' => $accessories,
                'size' => 'One Size',
                'color' => 'Black'
            ]
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setImage($productData['image']);
            $product->setStock($productData['stock']);
            $product->setCategory($productData['category']);
            $product->setSize($productData['size']);
            $product->setColor($productData['color']);
            $manager->persist($product);
        }

        $manager->flush();
    }
} 