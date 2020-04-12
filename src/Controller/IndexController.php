<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Product;
use App\Entity\User;
use App\Form\InfosClientType;
use App\Form\InscriptionType;
use App\Form\UpdatePasswordType;
use App\Repository\OrderDetailRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function listProduct(ProductRepository $productRepository)
    {

        $products = $productRepository->findAll();

        return $this->render('index/index.html.twig', [
            "products" => $products
        ]);
    }


    /**
     * @Route("/product/{id}", name="details_product")
     */
    public function detailsProduct(Product $product)
    {
        return $this->render('index/details/detailsProduct.html.twig', [
            "product" => $product
        ]);
    }

    /**
     * @Route("/panier/add/{id}", name="add_panier")
     */
    public function addPanier($id, SessionInterface $sessionInterface)
    {
        $panier = $sessionInterface->get('panier', []);
        // $user = $userInterface->getUsername();
        // $infos = $this->get('id')->getToken()->getUser();
        // $user = $this->getUser();
        // dd($user);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $sessionInterface->set('panier', $panier);
        // dd($user);
        return $this->redirectToRoute('panier');
    }

    /**
     * @Route("/panier", name="panier")
     */
    public function panier(SessionInterface $sessionInterface, ProductRepository $productRepository)
    {
        $panier = $sessionInterface->get('panier', []);

        $panierData = [];

        foreach ($panier as $id => $quantity) {

            $panierData[] = [ 
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }
       
        $total = 0;

        foreach ($panierData as $item) {
            $totalItem = $item['product']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }


        return $this->render('panier/panier.html.twig', [
            'items' => $panierData,
            'total' => $total
        ]);
    }

    /**
     * @Route("/panier/remove/{id}", name="panier_remove")
     */
    public function remove($id, SessionInterface $sessionInterface) 
    {
        $panier = $sessionInterface->get('panier', []);

        if(!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $sessionInterface->set('panier', $panier);

        return $this->redirectToRoute('panier');
    }

    /**
     * @Route("/order", name="order")
     */
    public function order(SessionInterface $sessionInterface, ProductRepository $productRepository) 
    {
        $panier = $sessionInterface->get('panier', []);
        foreach ($panier as $id => $quantity) {

            $panierData[] = [ 
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }
        $user = $this->getUser();
        // dd($user);

        if ($user == null) {

            return $this->redirectToRoute('login');
            
        } else {

            return $this->render('order/order.html.twig', [
                'items' => $panierData,
                'user' => $user
                ]);
            }
    }

    /**
     * @Route("/order/confirm", name="order_confirm")
     */
    public function orderConfirm(SessionInterface $sessionInterface, ProductRepository $productRepository, UserRepository $userRepository, EntityManagerInterface $manager, OrderDetailRepository $orderDetailRepository) 
    {
        $panier = $sessionInterface->get('panier', []);

        foreach ($panier as $id => $quantity) {

            $panierData[] = [ 
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }
        // dd($panierData['product'] => $productRepository->find($id));
        $user = [$this->getUser()];
        $findId = [];
        foreach ($user as $id) {

            $findId = [
                'id_user' => $userRepository->find($id)->getId()
            ];
        }
        // dd($panierData[0]['quantity']);
        $id_user = $findId['id_user'];
        // dd($id_user);
        // $osef = new User();
        // $osef->getId();
        // dd($osef);


        $order = new Order();
        $order->setDate(new \DateTime());
        $order->setUser($this->getUser());
        // dd($this->getDoctrine()->getRepository(Order::class)->findAll());
        $manager->persist($order);
        $manager->flush();

        // dd($order->getId());
        // $order_id = $order->getId();
        
        // dd($order_detail->setOrderId($order));
        
        for ($i=0; $i < count($panierData); $i++) {

            $order_detail = new OrderDetail();

            $order_detail->setOrderId($order);
            $order_detail->setProduct($panierData[$i]['product']);
            $order_detail->setPrice($panierData[$i]['product']);
            $order_detail->setQuantity($panierData[$i]['quantity']);
            $manager->persist($order_detail);
            $manager->flush();
        }

        return $this->render('order/orderConfirm.html.twig', [
            'order' => $order,
            'items' => $panierData
        ]);
    }

    // methods Mon Compte

    /**
     * @Route("/mon_compte", name="compte")
     */
    public function monCompte()
    {
        $user = $this->getUser();


        return $this->render('index/client/compteClient.html.twig', [
            "user" => $user
        ]);
    }

    
    /**
     * @Route("/mon_compte/infos", name="update_infos")
     */
    public function updateInfos(EntityManagerInterface $manager, HttpFoundationRequest $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(InfosClientType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute("compte");
        }


        return $this->render('index/client/updateInfosClient.html.twig', [
            "user" => $user,
            "form" => $form->createView()
        ]);
    }
    /**
     * @Route("/mon_compte/password", name="update_password")
     */
    public function updatePassword(EntityManagerInterface $manager, HttpFoundationRequest $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();

        $form = $this->createForm(UpdatePasswordType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $passwordCrypte = $encoder->encodePassword($user, $this->getUser()->getPassword());
            $user->setPassword($passwordCrypte);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute("compte");
        }


        return $this->render('index/client/updatePassword.html.twig', [
            "user" => $user,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(EntityManagerInterface $manager, HttpFoundationRequest $request, UserPasswordEncoderInterface $encoder)
    {

        $user = new User();

        $form = $this->createForm(InscriptionType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $passwordCrypte = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($passwordCrypte);
            $user->setRoles("ROLE_USER");
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute("index");
        }

        return $this->render('index/login/inscription.html.twig', [
            "form" => $form->createView()
        ]);
    }

     /**
     * @Route("/login ", name="login")
     */
    public function login(AuthenticationUtils $util, SessionInterface $sessionInterface) 
    {
        $panier = $sessionInterface->get('panier');


            return $this->render('index/login/login.html.twig', [
                "lastUserName" => $util->getLastUsername(),
                "error" => $util->getLastAuthenticationError()
            ]);
        
    }

    /**
     * @Route("/logout ", name="logout")
     */
    public function logout() {
        
    }
    
}