<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{


    #[Route('/', name: 'app_product')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        $products = $em->getRepository(Product::class)->findAll();

        $session = $request->getSession();
        $notification = $session->get('notification');
        $type_notif = $session->get('type_notif');

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'notification' => $notification,
            'type_notif' => $type_notif,
        ]);
    }

    #[Route('/product/{id_product}', name: 'view_product')]
    public function viewArticle($id_product, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $article = $em->getRepository(Product::class)->find($id_product);
        return $this->render('product/viewProduct.html.twig', [
            'article' => $article,
        ]);
    }

    
}
