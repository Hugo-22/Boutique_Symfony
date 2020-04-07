<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('index/index.html.twig');
    }

    /**
     * @Route("/index/login", name="inscription")
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
     * @Route("/index/login/1", name="login")
     */

    public function login(AuthenticationUtils $util) {

        return $this->render('index/login/login.html.twig', [
            "lastUserName" => $util->getLastUsername(),
            "error" => $util->getLastAuthenticationError()
        ]);

     }
}