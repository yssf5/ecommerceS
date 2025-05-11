<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartWithData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                // Vérifier si le produit est toujours en stock
                if ($product->getStock() > 0) {
                    $cartWithData[] = [
                        'product' => $product,
                        'quantity' => $quantity
                    ];
                    $total += $product->getPrice() * $quantity;
                } else {
                    // Supprimer le produit du panier s'il n'est plus en stock
                    unset($cart[$id]);
                    $session->set('cart', $cart);
                    $this->addFlash('warning', 'Some products were removed from your cart because they are out of stock.');
                }
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add($id, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_product_index');
        }

        if ($product->getStock() <= 0) {
            $this->addFlash('error', 'This product is out of stock.');
            return $this->redirectToRoute('app_product_index');
        }

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            if ($cart[$id] >= $product->getStock()) {
                $this->addFlash('error', 'Maximum stock reached for this product.');
                return $this->redirectToRoute('app_cart');
            }
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $session->set('cart', $cart);
        $this->addFlash('success', 'Product added to cart successfully.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Product removed from cart successfully.');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/increase/{id}', name: 'app_cart_increase')]
    public function increase($id, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            if ($cart[$id] >= $product->getStock()) {
                $this->addFlash('error', 'Maximum stock reached for this product.');
                return $this->redirectToRoute('app_cart');
            }
            $cart[$id]++;
            $session->set('cart', $cart);
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
            $session->set('cart', $cart);
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        $this->addFlash('success', 'Cart cleared successfully.');

        return $this->redirectToRoute('app_cart');
    }
} 