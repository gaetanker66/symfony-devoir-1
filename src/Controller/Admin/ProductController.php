<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\ByteString;

#[Route('/admin')]
class ProductController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository, private RequestStack $requestStack, private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/product', name: 'admin.product.index')]
    public function index(): Response
    {

        return $this->render('admin/product/index.html.twig', [
            'products' => $this->productRepository->findAll(),
        ]);
    }

    #[Route('/product/form', name: 'admin.product.form')]
    #[Route('/product/update/{id}', name: 'admin.product.update')]
    public function form(int $id = null): Response
    {
        $entity = $id ? $this->productRepository->find($id) : new Product();
        if ($id && !$entity) {
            $this->addFlash('danger', 'Le produit demandé n\'existe pas');
            return $this->redirectToRoute('admin.product.index');
        }
        $message = $id ? 'Le produit a bien été modifié' : 'Le produit a bien été ajouté';
        $type = ProductType::class;

        $entity->prevImage = $entity->getImage();
        $form = $this->createForm($type, $entity);

        // récupérer la saisie précédente dans la requête HTTP

        $form->handleRequest($this->requestStack->getMainRequest());

        // si le formulaire a été envoyé et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            // dd($entity);
            $filename = ByteString::fromRandom(32)->lower();
            $file = $entity->getImage();

            if ($file instanceof UploadedFile){
                $fileExtension = $file->guessClientExtension();
                $file->move('img', "$filename.$fileExtension");
                $entity->setImage("$filename.$fileExtension");

                if ($id){
                    if (file_exists("img/{$entity->prevImage}"))
                        unlink("img/{$entity->prevImage}");
                }
            } else{
                $entity->setImage($entity->prevImage);
            }





            // insérer dans la base de données
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->addFlash('success', $message);

            // rediriger vers la liste
            return $this->redirectToRoute('admin.product.index');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/delete/{id}', name: 'admin.product.delete')]
    public function delete(int $id): RedirectResponse
    {
        $entity = $this->productRepository->find($id);
        if (!$entity) {
            $this->addFlash('danger', 'Le produit demandé n\'existe pas');
            return $this->redirectToRoute('admin.product.index');
        }
        if (file_exists("img/{$entity->getImage()}"))
            unlink("img/{$entity->getImage()}");

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le produit a bien été supprimé');

        return $this->redirectToRoute('admin.product.index');
    }
}
