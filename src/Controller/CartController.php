<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $id = $product->getId();

        if (!isset($cart[$id])) {
            $cart[$id] = 0;
        }
        $cart[$id]++;

        $request->getSession()->set('cart', $cart);

        $this->addFlash('success', 'Produit ajouté au panier avec succès.');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/save-orders', name: 'app_cart_save_orders', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function saveOrders(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $cart = $request->getSession()->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart_index');
        }

        $user = $this->getUser();
        $shippingAddress = $request->request->get('shipping_address');

        if (!$shippingAddress) {
            $this->addFlash('error', 'Veuillez fournir une adresse de livraison');
            return $this->redirectToRoute('app_cart_index');
        }

        $totalAmount = 0;
        $order = new Order();
        $order->setUser($user);
        $order->setStatus('pending');
        $order->setOrderDate(new \DateTime());
        $order->setShippingAddress($shippingAddress);

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $orderItem = new OrderItem();
                $orderItem->setOrder($order);
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setPrice($product->getPrice());
                
                $totalAmount += $product->getPrice() * $quantity;
                
                $entityManager->persist($orderItem);
            }
        }

        $order->setTotalAmount($totalAmount);
        $entityManager->persist($order);
        $entityManager->flush();

        // Clear the cart after saving orders
        $request->getSession()->remove('cart');

        $this->addFlash('success', 'Vos commandes ont été enregistrées avec succès.');
        return $this->redirectToRoute('app_profile_orders');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Request $request, Product $product): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $request->getSession()->set('cart', $cart);

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request): Response
    {
        $request->getSession()->remove('cart');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/increase/{id}', name: 'app_cart_increase')]
    public function increase(Request $request, Product $product): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            $cart[$id]++;
        }

        $request->getSession()->set('cart', $cart);

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease(Request $request, Product $product): Response
    {
        $cart = $request->getSession()->get('cart', []);
        $id = $product->getId();

        if (isset($cart[$id])) {
            if ($cart[$id] > 1) {
                $cart[$id]--;
            } else {
                unset($cart[$id]);
            }
        }

        $request->getSession()->set('cart', $cart);

        return $this->redirectToRoute('app_cart_index');
    }
} 