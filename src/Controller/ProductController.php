<?php

namespace App\Controller;

use App\Model\Search;
use App\Form\SearchType;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/articles', name: 'product')]
public function index(ProductRepository $repository, Request $request, PaginatorInterface $paginator): Response
{
    $search = new Search();
    $form = $this->createForm(SearchType::class, $search);
    $form->handleRequest($request);

    // Récupérer les produits correspondant à la recherche
    $query = $repository->findWithSearch($search);

    // Paginer les résultats
    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        9
    );

    return $this->render('product/index.html.twig', [
        'pagination' => $pagination,
        'form' => $form->createView(),
    ]);
}





    #[Route('/articles/{slug}', name: 'product_show')]
    public function show(ProductRepository $repository, string $slug): Response
    {
        $product = $repository->findOneBySlug($slug);

        if (!$product) {
            return $this->redirectToRoute('product');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
