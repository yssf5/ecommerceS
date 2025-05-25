<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'app_admin_orders_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        $status = $request->query->get('status');
        $criteria = [];
        
        if ($status) {
            $criteria['status'] = $status;
        }

        $orders = $orderRepository->findBy($criteria, ['orderDate' => 'DESC']);

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_orders_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/update-status', name: 'app_admin_orders_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $status = $request->request->get('status');
        $trackingNumber = $request->request->get('trackingNumber');

        if ($status) {
            $order->setStatus($status);
        }

        if ($trackingNumber) {
            $order->setTrackingNumber($trackingNumber);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Order status has been updated successfully.');

        return $this->redirectToRoute('app_admin_orders_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/details', name: 'app_admin_orders_details', methods: ['GET'])]
    public function details(Order $order): Response
    {
        return $this->render('admin/order/details.html.twig', [
            'order' => $order,
        ]);
    }
} 