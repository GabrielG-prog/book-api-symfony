<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class AuthorController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des auteurs. 
     *
     * @param AuthorRepository $authorRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/authors', name: 'authors', methods: ['GET'])]
    public function getAllAuthors(AuthorRepository $authorRepository, SerializerInterface $serializer, 
        Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        
        $idCache = "getAllAuthor-" . $page . "-" . $limit;

        $jsonAuthorList = $cache->get($idCache, function (ItemInterface $item) use ($authorRepository, $page, $limit, $serializer) {
            //echo ("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
            $item->tag("booksCache");
            $authorList = $authorRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(["getAuthors"]);
            return $serializer->serialize($authorList, 'json', $context);
        });
        
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }
    
    /**
     * Cette méthode permet de récupérer un auteur en particulier en fonction de son id. 
     *
     * @param AuthorRepository $authorRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
	#[Route('/api/authors/{id}', name: 'detailAuthor', methods: ['GET'])]
    public function getDetailAuthor(int $id, SerializerInterface $serializer, AuthorRepository $authorRepository): JsonResponse {
        $author = $authorRepository->find($id);
        if ($author) {
            $context = SerializationContext::create()->setGroups(["getAuthors"]);
            $jsonAuthor = $serializer->serialize($author, 'json', $context);
            return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    /**
     * Cette méthode supprime un auteur en fonction de son id. 
     * En cascade, les livres associés aux auteurs seront aux aussi supprimés. 
     * 
     * @param Author $author
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un auteur')]
    public function deleteAuthor(Author $author, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse {
        
        $em->remove($author);
        $em->flush();

        // Vidage du cache
        $cache->invalidateTags(["booksCache"]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de créer un nouvel auteur. Elle ne permet pas 
     * d'associer directement des livres à cet auteur. 
     * Exemple de données :
     * {
     *     "lastName": "John",
     *     "firstName": "Doe"
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('/api/authors', name: 'createAuthor', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un auteur')]
    public function createAuthor(Request $request, SerializerInterface $serializer,
        EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator,
        TagAwareCacheInterface $cache): JsonResponse {
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');
        
        // Vérification des erreurs
        $errors = $validator->validate($author);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($author);
        $em->flush();
        
        // Vidage du cache 
        $cache->invalidateTags(["booksCache"]);

        $context = SerializationContext::create()->setGroups(["getAuthors"]);
        $jsonAuthor = $serializer->serialize($author, 'json', $context);
        $location = $urlGenerator->generate('detailAuthor', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["Location" => $location], true);	
    }

    
    /**
     * Cette méthode permet de mettre à jour un auteur. 
     * Exemple de données :
     * {
     *     "lastName": "Johnny",
     *     "firstName": "Does"
     * }
     * 
     * Cette méthode ne permet pas d'associer des livres et des auteurs.
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Author $currentAuthor
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/authors/{id}', name:"updateAuthor", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer un auteur')]
    public function updateAuthor(Request $request, SerializerInterface $serializer,
        Author $currentAuthor, EntityManagerInterface $em, ValidatorInterface $validator,
        TagAwareCacheInterface $cache): JsonResponse {

        // Vérification des erreurs
        $errors = $validator->validate($currentAuthor);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $newAuthor = $serializer->deserialize($request->getContent(), Author::class, 'json');
        $currentAuthor->setFirstName($newAuthor->getFirstName());
        $currentAuthor->setLastName($newAuthor->getLastName());
        
        $em->persist($currentAuthor);
        $em->flush();

        // Vidage du cache
        $cache->invalidateTags(["booksCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

    }
}
