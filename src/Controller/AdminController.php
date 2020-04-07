<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;


class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/admin.html.twig');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function createCategory(EntityManagerInterface $manager, HttpFoundationRequest $request)
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/category.html.twig', [
            "category" => $category,
            "formCat" => $form->createView()
        ]);
    }



     /**
     * @Route("/admin/product", name="add_product")
     * @Route("/admin/product/{id}", name="product_update", methods="GET|POST")
     */
    public function createUpdate(EntityManagerInterface $manager, HttpFoundationRequest $request)
    {
        // if(!$product) {

        // }
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            // $product->setIsPublished(true);

            $manager->persist($product);
            $manager->flush();

            return $this->redirectToRoute("admin");
        }

        return $this->render('admin/product/createUpdate.html.twig', [
            "product" => $product,
            "formP" => $form->createView()
        ]);
    }
}