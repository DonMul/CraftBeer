<?php

namespace App\Controller;

use App\Entity\Beer;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    /** KernelInterface $appKernel */
    private $appKernel;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $beers = $em->getRepository(Beer::class)->findAll();
        return $this->render('admin/main.html.twig', [
            'beers' => $beers,
        ]);
    }

    /**
     * @Route("/admin/beer/{id}")
     */
    public function editBeer(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = $request->get('id');
        $beer = $em->getRepository(Beer::class)->findOneBy(['id' => $id]);

        if (($beer instanceof Beer) === false) {
            $beer = new Beer();
        }

        return $this->render('admin/beer.html.twig', [
            'beer' => $beer,
            'categories' => $em->getRepository(Category::class)->findAll(),
            'brands' => $em->getRepository(Brand::class)->findAll(),
        ]);
    }

    /**
     * @Route("/admin/categories")
     */
    public function categories(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/category.html.twig', [
            'categories' => $em->getRepository(Category::class)->findAll(),
        ]);
    }

    /**
     * @Route("/admin/users")
     */
    public function users(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/users.html.twig', [
            'users' => $em->getRepository(User::class)->findAll(),
        ]);
    }

    /**
     * @Route("/service/admin/user/enable-role")
     */
    public function addRoleToUser(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $request->request->get('userId')
        ]);

        if (($user instanceof User) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Gebruiker niet gevonden'
                ],
            ]);
        }

        $roles = $user->getRoles();
        $roles[] = $request->request->get('role');
        $user->setRoles($roles);

        $user->setRoles($roles);
        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,

        ]);
    }

    /**
     * @Route("/service/admin/user/remove-role")
     */
    public function removeRoleFromUser(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $request->request->get('userId')
        ]);

        if (($user instanceof User) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Gebruiker niet gevonden'
                ],
            ]);
        }

        $roles = $user->getRoles();
        foreach ($roles as $key => $role) {
            if ($role != $request->request->get('role')) {
                continue;
            }

            unset($roles[$key]);
        }

        $user->setRoles($roles);
        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,

        ]);
    }

    /**
     * @Route("/service/admin/user/delete")
     */
    public function removeUser(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $request->request->get('id')
        ]);

        if (($user instanceof User) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Gebruiker niet gevonden'
                ],
            ]);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,

        ]);
    }

    /**
     * @Route("/service/admin/category/add")
     */
    public function addCategory(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $category = $em->getRepository(Category::class)->findOneBy([
            'name' => $request->request->get('name')
        ]);

        if (($category instanceof Category)) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Een categorie met die naam bestaat al'
                ],
            ]);
        }

        $category = new Category();
        $category->setName($request->request->get('name'));

        $em->persist($category);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,
        ]);
    }

    /**
     * @Route("/service/admin/category/delete")
     */
    public function removeCategory(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $category = $em->getRepository(Category::class)->findOneBy([
            'id' => $request->request->get('id')
        ]);

        if (($category instanceof Category) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Categorie niet gevonden'
                ],
            ]);
        }

        $em->remove($category);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,

        ]);
    }

    /**
     * @Route("/service/admin/beer/delete")
     */
    public function removeBeer(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $beer = $em->getRepository(Beer::class)->findOneBy([
            'id' => $request->request->get('id')
        ]);

        if (($beer instanceof Beer) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Bier niet gevonden'
                ],
            ]);
        }

        $em->remove($beer);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,

        ]);
    }

    /**
     * @Route("/service/admin/category/edit")
     */
    public function editCategory(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $category = $em->getRepository(Category::class)->findOneBy([
            'id' => $request->request->get('id')
        ]);

        if (($category instanceof Category) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Categorie niet gevonden'
                ],
            ]);
        }

        $category->setName($request->request->get('name'));
        $em->persist($category);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,

        ]);
    }


    /**
     * @Route("/service/admin/beer/toggle-stock")
     */
    public function toggleBeerStock(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $beer = $em->getRepository(Beer::class)->findOneBy([
            'id' => $request->request->get('id')
        ]);

        if (($beer instanceof Beer) === false) {
            return $this->json([
                'success' => false,
                'faults' => [
                    'Bier niet gevonden'
                ],
            ]);
        }

        $beer->setInStock(!$beer->getInStock());
        $em->persist($beer);
        $em->flush();

        return $this->json([
            'success' => true,
            'reload' => true,

        ]);
    }

    /**
     * @Route("/service/admin/beer/save")
     */
    public function saveBeer(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $beer = $em->getRepository(Beer::class)->findOneBy([
            'id' => $request->request->get('id')
        ]);

        if (($beer instanceof Beer) === false) {
            $beer = new Beer();
            $beer->setImage('');
        }

        $beer->setName($request->request->get('name'));
        $beer->setAbv($request->request->get('abv'));
        $beer->setPrice($request->request->get('price'));
        $beer->setInStock(true);
        $newBrand = trim($request->request->get('newBrand'));

        $brand = $em->getRepository(Brand::class)->findOneBy([
            'id' => $request->request->get('existingBrand')
        ]);

        if (($brand instanceof Brand) === false && strlen($newBrand) > 0) {
            $brand = $em->getRepository(Brand::class)->findOneBy([
                'name' => $newBrand
            ]);

            if (($brand instanceof Brand) === false) {
                $brand = new Brand();
                $brand->setName($newBrand);
                $em->persist($brand);
                $em->flush();
            }
        }
        $beer->setBrand($brand);
        $beer->setShortDescription(trim($request->request->get('shortDescription')));
        $beer->setLongDescription(trim($request->request->get('longDescription')));
        $beer->setSlug(urlencode($brand->getName() . '-' . $beer->getName()));

        $file = $request->files->get('image');

        if ($file) {
            try {
                $imageExtension = $file->guessExtension();
            } catch (\Exception $ex) {
                $imageExtension = '';
            }

            $dir = $this->appKernel->getProjectDir() . '/public/img/upload/';
            if (file_exists($dir) === false) {
                mkdir($dir, 0777);
            } else {
                chmod($dir, 0777);
            }

            $newFilename = $beer->getId() . '.' . $imageExtension;
            $file->move(
                $dir,
                $newFilename
            );

            if (file_exists($dir . DIRECTORY_SEPARATOR . $newFilename)) {
                chmod($dir . DIRECTORY_SEPARATOR . $newFilename, 0777);
            }

            $beer->setImage($newFilename);
        }

        foreach (($request->request->get('category', []) ?? []) as $categoryId) {
            $category = $em->getRepository(Category::class)->findOneBy([
                'id' => $categoryId
            ]);

            if (($category instanceof Category) === false) {
                continue;
            }

            $beer->addCategory($category);
        }

        $em->persist($beer);
        $em->flush();

        return $this->json([
            'success' => true,
            'redirect' => '/admin'
        ]);
    }
}
