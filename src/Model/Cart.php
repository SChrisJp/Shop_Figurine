<?php
namespace App\Model;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Permet de gérer un panier en session plutôt que de tout implémenter dans le contrôleur
 */
class Cart 
{
    private $session;
    private $repository;

    public function __construct(SessionInterface $session, ProductRepository $repository)
    {
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * Crée un tableau associatif id => quantité et le stocke en session
     *
     * @param int $id
     * @return void
     */
    public function add(int $id): void
    {
        $cart = $this->session->get('cart', []);

        if (empty($cart[$id])) {
            $cart[$id] = 1;
        } else {
            $cart[$id]++;
        }

        $this->session->set('cart', $cart);
    }

    /**
     * Récupère le panier en session
     *
     * @return array
     */
    public function get(): array
    {
        return $this->session->get('cart', []);
    }

    /**
     * Supprime entièrement le panier en session
     *
     * @return void
     */
    public function clear(): void
    {
        $this->session->remove('cart');

        dump('Le panier a été réinitialisé.');
    }

    /**
     * Supprime entièrement un produit du panier (quelque soit sa quantité)
     *
     * @param int $id
     * @return void
     */
    public function removeItem(int $id): void
    {
        $cart = $this->session->get('cart', []);
        unset($cart[$id]);
        $this->session->set('cart', $cart);
    }

    /**
     * Diminue de 1 la quantité d'un produit
     *
     * @param int $id
     * @return void
     */
    public function decreaseItem(int $id): void
    {
        $cart = $this->session->get('cart', []);
        if ($cart[$id] < 2) {
            unset($cart[$id]);
        } else {
            $cart[$id]--;
        }
        $this->session->set('cart', $cart);
    }

    /**
     * Récupère le panier en session, puis récupère les objets produits de la base de données
     * et calcule les totaux
     *
     * @return array
     */
    public function getDetails(): array
    {
        $cartProducts = [
            'products' => [],
            'totals' => [
                'quantity' => 0,
                'price' => 0,
            ],
        ];

        $cart = $this->session->get('cart', []);
        if ($cart) {
            foreach ($cart as $id => $quantity) {
                $currentProduct = $this->repository->find($id);
                if ($currentProduct) {
                    $cartProducts['products'][] = [
                        'product' => $currentProduct,
                        'quantity' => $quantity
                    ];
                    $cartProducts['totals']['quantity'] += $quantity;
                    $cartProducts['totals']['price'] += $quantity * $currentProduct->getPrice();
                }
            }
        }
        return $cartProducts;
    }

      /**
     * Récupère la quantité totale des articles dans le panier
     *
     * @return int
     */
    public function getTotalQuantity(): int
    {
        $cart = $this->session->get('cart', []);
        $totalQuantity = 0;

        foreach ($cart as $quantity) {
            $totalQuantity += $quantity;
        }

        return $totalQuantity;
    }

    
    
}
