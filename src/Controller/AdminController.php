<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Form\CategoryType;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/admin/category", name="liste_cat")
     */
    public function listCategory(CategoryRepository $categoryRepository)
    {

        $categories = $categoryRepository->findAll();

        return $this->render('admin/category/category.html.twig', [
            "categories" => $categories
        ]);
    }

    /**
     * @Route("/admin/category/add", name="add_category")
     * @Route("/admin/category/add/{id}", name="update_category", methods="GET|POST")
     */
    public function createUpdateCategory(Category $category = null, EntityManagerInterface $manager, HttpFoundationRequest $request)
    {
        if(!$category) {

            $category = new Category();
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute('liste_cat');
        }

        return $this->render('admin/category/add/newCategory.html.twig', [
            "category" => $category,
            "formCat" => $form->createView()
        ]);
    }

     /**
     * @Route("/admin/category/{id}", name="delete_cat", methods="SUP")
     */
    public function deleteCategory(Category $category, HttpFoundationRequest $request, EntityManagerInterface $objectManager) {

        if($this->isCsrfTokenValid("SUP".$category->getId(), $request->get('_token'))) {
            $objectManager->remove($category);
            $objectManager->flush();
            $this->addFlash('success', "L'action a été effectué");
            return $this->redirectToRoute("liste_cat");
        }
     }



    //  METHODS PRODUITS

    /**
     * @Route("/admin/product", name="liste_product")
     */
    public function listProduct(ProductRepository $productRepository)
    {

        $products = $productRepository->findAll();

        return $this->render('admin/product/product.html.twig', [
            "products" => $products
        ]);
    }

     /**
     * @Route("/admin/product/add", name="add_product")
     * @Route("/admin/product/add/{id}", name="product_update", methods="GET|POST")
     */
    public function createUpdateProduct(Product $product = null, EntityManagerInterface $manager, HttpFoundationRequest $request)
    {
        if(!$product) {
            $product = new Product();
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            // $product->setIsPublished(true);

             // traitement de l'image
             if($product->getImage() !==null) {

                 $file = $form->get('image')->getData();

                 $filename = uniqid(). '.' . $file->guessExtension();
                try {
                    $file->move($this->getParameter('images_directory'), $filename);
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $product->setImage($filename);

             }

            $manager->persist($product);
            $manager->flush();

            return $this->redirectToRoute("liste_product");
        }

        return $this->render('admin/product/add/newProduct.html.twig', [
            "product" => $product,
            "formP" => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/product/{id}", name="delete_product", methods="SUP")
     */
    public function deleteProduct(Product $product, HttpFoundationRequest $request, EntityManagerInterface $objectManager) {

        if($this->isCsrfTokenValid("SUP".$product->getId(), $request->get('_token'))) {
            $objectManager->remove($product);
            $objectManager->flush();
            $this->addFlash('success', "L'action a été effectué");
             return $this->redirectToRoute("liste_product");
        }
     }

     // METHODS CLIENTS


        /**
     * @Route("/admin/user/{id}", name="details_user")
     */
    public function detailsClient(User $user)
    {
        return $this->render('admin/user/detailsUser.html.twig', [
            "user" => $user
        ]);
    }

      /**
     * @Route("/admin/user", name="liste_user")
     */
    public function listClient(UserRepository $userRepository)
    {

        $users = $userRepository->findAll();

        return $this->render('admin/user/user.html.twig', [
            "users" => $users
        ]);
    }

    // methods orders 

     /**
     * @Route("/admin/order", name="")
     */
    public function listOrder(OrderRepository $orderRepository)
    {

        $orders = $orderRepository->findAll();

        return $this->render('admin/order/order.html.twig', [
            "order" => $orders        
        ]);
    }

}