<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CheckoutType;
use App\Form\ProductType;

use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{


    #[Route('/', name: 'list_products')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        $products = $em->getRepository(Product::class)->findAll();

        $session = $request->getSession();
     

        return $this->render('product/index.html.twig', [
            'products' => $products,
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


    #[Route('/admin-register', name: 'admin_register')]
    public function register(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if ($request->isMethod('POST')) {
            $session = $request->getSession();

            if (!$session->has('admin_credentials')) {
                $session->set('admin_credentials', []);
            }

            $adminCredentials = $session->get('admin_credentials');
            $adminCredentials[$username] = $password;

            $session->set('admin_credentials', $adminCredentials);

            return $this->redirectToRoute('admin_login'); // Rediriger vers la page de login admin
        }
        return $this->render('product/viewFormRegister.html.twig', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    #[Route('/admin-login', name: 'admin_login')]
    public function login(Request $request): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if ($request->isMethod('POST')) {
            $session = $request->getSession();

            if (!$session->has('admin_credentials')) {
                $session->set('admin_credentials', []);
            }

            $adminCredentials = $session->get('admin_credentials');

            if (array_key_exists($username, $adminCredentials) && $adminCredentials[$username] === $password) {
                $session->set('admin', $username);
                return $this->redirectToRoute('admin_dashboard');
            }
        }
        return $this->render('product/viewFormLogin.html.twig', [
            'username' => $username,
            'password' => $password,
        ]);
    }

    #[Route('/admin-dashboard', name: 'admin_dashboard')]
    public function getProductsAdmin(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $products = $em->getRepository(Product::class)->findAll();
        $session = $request->getSession();

        return $this->render('product/viewDashboard.html.twig', [
            'products' => $products,
        ]);
    }


    #[Route('/admin-add-product', name: 'admin_add_product')]
    public function addProduct(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $em->persist($product);
            $em->flush();

            $session = $request->getSession();

            return $this->redirectToRoute('list_products');
        }

        return $this->render('product/viewFormAddProduct.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //    update product 
    #[Route('/admin-edit-product/{id_product}', name: 'admin_edit_product')]
    public function editProduct($id_product, ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();

        $product = $em->getRepository(Product::class)->find($id_product);

        if ($product === null) {
            return $this->redirectToRoute('list_products');
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                // Le bouton Valider a été cliqué
                $em->flush();

                $session = $request->getSession();
              
                return $this->redirectToRoute('admin_dashboard');
            } elseif ($form->get('delete')->isClicked()) {
                // Le bouton Supprimer a été cliqué
                $em->remove($product);
                $em->flush();

                $session = $request->getSession();
                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('product/viewFormEditProduct.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
