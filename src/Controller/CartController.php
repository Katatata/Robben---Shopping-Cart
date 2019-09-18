<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Invoice;
use App\Entity\Bestelling;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * @Route("/cart")
 */
class CartController extends AbstractController
{
    private $session;
    private $cart;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    
    /**
     * @Route("/", name="cart")
     */
    public function index()
    {
        $product = $this->getDoctrine()->getRepository(Product::class);
        $getCart = $this->session->get('cart');
        $empty = false;

        if (!isset($getCart)) {
            $empty = true;
        }
        else if(empty($getCart)) {
            $empty = true;
            $getCart = null;
        };

        if(isset($getCart)) {
            foreach ($getCart as $item) {
                $id = $item['id'];
    
                $getCart[$id]['naam'] = $product->find($id)->getName();
                $getCart[$id]['prijs'] = ($product->find($id)->getPrice() * $getCart[$id]['aantal']);
            };

            $value = number_format(array_sum(array_column($getCart,'prijs')), 2, ',', ' ');

            $this->cart = $getCart;
            
            return $this->render('cart/index.html.twig', [
                'controller_name' => 'CartController',
                'cart' => $getCart,
                'empty' => $empty,
                'total' => $value
            ]);
        }


        // return new Response($product->getPrijs());

        // $response = $this->forward('App\Controller\ProductController::data', [
        //     'id'  => 1,
        // ]);

        // print_r($product->find(2)->getPrijs());

        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'cart' => $getCart,
            'empty' => $empty
        ]);
    }

        
    /**
     * @Route("/clear", name="clear")
     */
    public function clear()
    {
        $this->session->set('cart', null);

        // return $this->render('cart/clear.html.twig', [
        //     'controller_name' => 'CartController',
        // ]);

        return $this->redirect($this->generateUrl('cart'));
    }

        
    /**
     * @Route("/{id}/min", name="min")
     */
    public function min($id)
    {
        $getCart = $this->session->get('cart');

        if($getCart[$id]['aantal'] === 1 ) {
            unset($getCart[$id]);
        }
        else {
            $getCart[$id]['aantal']--;
        };

        $this->session->set('cart', $getCart);

        return $this->redirect($this->generateUrl('cart'));
    }
        
    /**
     * @Route("/{id}/update/{quantity}", name="update")
     */
    public function update($id, $quantity)
    {
        $getCart = $this->session->get('cart');


        $getCart[$id]['aantal'] = $quantity;

        $this->session->set('cart', $getCart);

        return $this->redirect($this->generateUrl('cart'));
    }

        
    /**
     * @Route("/{id}/remove", name="remove")
     */
    public function remove($id)
    {
        $getCart = $this->session->get('cart');

        unset($getCart[$id]);

        $this->session->set('cart', $getCart);

        return $this->redirect($this->generateUrl('cart'));
    }

        
    /**
     * @Route("/checkout", name="checkout")
     */
    public function checkout(Request $request, \Swift_Mailer $mailer)
    {

        $form = $this->createFormBuilder()
        ->add('save', SubmitType::class, ['label' => 'Submit'])
        ->getForm();

        $getCart = $this->session->get('cart');
        $product = $this->getDoctrine()->getRepository(Product::class);

        $auth_checker = $this->get('security.authorization_checker');
        $user =  $this->get('security.token_storage')->getToken()->getUser();

        if($auth_checker->isGranted('IS_AUTHENTICATED_FULLY') === true) {
            if(isset($getCart)) {
                foreach ($getCart as $item) {
                    $id = $item['id'];
        
                    $getCart[$id]['naam'] = $product->find($id)->getName();
                    $getCart[$id]['prijs'] = ($product->find($id)->getPrice() * $getCart[$id]['aantal']);
                };

                $value = number_format(array_sum(array_column($getCart,'prijs')), 2, ',', ' ');
                

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    $task = $form->getData();

                    $message = (new \Swift_Message('Hello Email'))
                    ->setFrom('send@example.com')
                    ->setTo($user->getEmail())
                    ->setBody($getCart);

                    $this->insertData($user, $getCart, $value);
                    $this->session->clear();

                    // return $this->redirectToRoute('cart');
                    return $this->render('cart/finished.html.twig', [
                        'user' => $user,
                    ]);
                }

                return $this->render('cart/checkout.html.twig', [
                    'form' => $form->createView(),
                    'user' => $user,
                    'cart' => $getCart,
                    'total' => $value
                ]);
            }
        }
        else {
            return $this->redirect('/login');
        };
    }

    public function insertData($user, $cart, $total) {
        $userId = $user->getId();
        $product = $this->getDoctrine()->getRepository(Product::class);
        $date = date("Y-m-d H:i:s");
        $invoice = new Invoice();
        
        $entityManager = $this->getDoctrine()->getManager();
        
        foreach ($cart as $item) {
            $id = $item['id'];
            
            $productId = $product->find($id);
            
            $order = new Bestelling();
            $order->setInvoiceId($invoice);
            $order->setAantal($cart[$id]['aantal']);
            $order->setProductId($productId);

            $invoice->setUserID($user);
            // $invoice->addOrderID($order);
            $invoice->setTax(26);
            $invoice->setDate(\DateTime::createFromFormat('Y-m-d', "2018-09-09"));
            $invoice->setTotalPrice($total);
    
            $entityManager->persist($order);
            $entityManager->persist($invoice);
            $entityManager->flush();
        };
    }
}
