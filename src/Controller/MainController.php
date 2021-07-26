<?php

namespace App\Controller;

use App\Entity\Beer;
use App\Entity\Brand;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $beers = $em->getRepository(Beer::class)->findAll();

        $brandsListed = [];
        foreach ($beers as $beer) {
            $brandsListed[] = $beer->getBrand()->getName();
        }

        $brandsListed = array_unique($brandsListed);
        sort($brandsListed);
        return $this->render('main.html.twig', [
            'brands' => $brandsListed,
            'beers' => $beers,
            'categories' => $em->getRepository(Category::class)->findAll(),
        ]);
    }

    /**
     * @Route("/bier/{slug}")
     */
    public function beer(Request $request, EntityManagerInterface $em): Response
    {
        $beer = $em->getRepository(Beer::class)->findOneBy(['slug' => $request->get('slug')]);
        return $this->render('beer.html.twig', [
            'beer' => $beer,
        ]);
    }
}
