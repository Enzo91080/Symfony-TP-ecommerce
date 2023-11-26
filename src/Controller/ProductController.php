<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CheckoutType;

use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{


    #[Route('/', name: 'list_products')]
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

    #[Route('/add-to-cart/{id_product}', name: 'add_to_cart')]
    public function addToCart($id_product, Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $product = $em->getRepository(Product::class)->find($id_product);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $session = $request->getSession();
        $panier = $session->get('panier');

        if (empty($panier)) {
            $panier = [];
        }

        if (array_key_exists($id_product, $panier)) {
            $panier[$id_product]++;
        } else {
            $panier[$id_product] = 1;
        }

        $session->set('panier', $panier);

        $session->set('notification', 'Le produit a bien été ajouté au panier');
        $session->set('type_notif', "alert-success");

        if (empty($session->get('panier'))) {
            throw $this->createNotFoundException('Panier vide');
        }

        return $this->redirectToRoute('cart');
    }


    #[Route('/cart', name: 'cart')]
    public function cart(Request $request, ManagerRegistry $doctrine): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier');

        $em = $doctrine->getManager();
        $products = [];

        if ($panier !== null) {
            $products = $em->getRepository(Product::class)->findBy(['id' => array_keys($panier)]);
        }

        return $this->render('product/viewCart.html.twig', [
            'products' => $products,
            'panier' => $panier ?: [], // Set $panier to an empty array if it's null
        ]);
    }

    #[Route('/checkout', name: 'checkout')]
    public function checkout(Request $request, ManagerRegistry $doctrine): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier');

        $em = $doctrine->getManager();
        $products = [];

        if ($panier !== null) {
            $products = $em->getRepository(Product::class)->findBy(['id' => array_keys($panier)]);
        }

        $form = $this->createForm(CheckoutType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $checkout = $form->getData();

            $session->set('checkout', $checkout);

            return $this->redirectToRoute('order_summary');
        }

        return $this->render('product/viewCheckout.html.twig', [
            'products' => $products,
            'panier' => $panier ?: [], // Set $panier to an empty array if it's null
            'form' => $form->createView(),
        ]);
    }

    #[Route('/order-summary', name: 'order_summary')]
    public function orderSummary(Request $request, ManagerRegistry $doctrine): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier');
        $checkout = $session->get('checkout');

        $em = $doctrine->getManager();
        $products = [];

        if ($panier !== null) {
            $products = $em->getRepository(Product::class)->findBy(['id' => array_keys($panier)]);
        }

        return $this->render('product/viewOrderSummary.html.twig', [
            'products' => $products,
            'panier' => $panier ?: [], // Set $panier to an empty array if it's null
            'checkout' => $checkout,
        ]);
    }

    #[Route('thank-you', name: 'thank_you')]
    public function viewThankYou(): Response
    {
        return $this->render('product/viewThankYou.html.twig');

    }
}
