<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Cocur\Slugify\Slugify;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    // J'AI BESOIN DE 'UserPasswordEncoderInterface' POUR ENCODER LE 'password'
    // LORS DE LA CRÉATION D'UN USER. DONX JE SUIS OBLIGÉ DE CRÉER UN CONTRUCTEUR
    // POUR LUI PASSER CETTE INTERFACE EN PARAMÈTRE ET DE CRÉER UNE PROPRIÉTÉ '$encoder'
    // QUI REPRÉSENTERA L'OBJET ENCODER DE L'INTERFACE 'UserPasswordEncoderInterface'
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        // use the factory to create a Faker\Generator instance
        $faker = Factory::create('fr_FR');
        $slugify = new Slugify();

        for ($u = 0; $u < 10; $u++) {
            $user = new User();
            $user->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail("user$u@gmail.com");
            $password = $this->encoder->encodePassword($user, "pass");
            $user->setPassword($password);
            // Je crée une boucle pour répéter la création d'articles (40 fois)
            for ($a = 0; $a < 10; $a++) {
                // Je crée un article
                $article = new Article();
                // Je manipule cet article
                $article->setTitle("Titre article")
                    ->setSlug($slugify->slugify($article->getTitle()))
                    ->setImage("http://placehold.it/400x200")
                    // Le contenu 'content' des article sera en markdown
                    ->setContent("Contenu de l'article")
                    ->setCreatedAt($faker->dateTimeBetween("-6months"))
                    ->setUser($user);
                if ($faker->boolean()) {
                    $article->getPublishedAt($faker->dateTimeBetween("-6 month"));
                    // On crée des fixture avec un nombre aléatoire de commentaire
                    $limit = mt_rand(0, 4);
                    for ($c = 0; $c < $limit; $c++) {
                        $comment = new Comment();
                        // J'alimente l'objet Comment que je viens de créer avec des données
                        // grace aux setter de l'entité Comment et au faker
                        $comment->setAuthorName($faker->firstName)
                            ->setContent($faker->realText(180))
                            ->setCreatedAt($faker->dateTimeBetween("-5 months"))
                            //la ligne ci-dessous dit : Voila l'artcile auquel je lie ce commentaire que je suis en train de créer
                            ->setArticle($article)
                            ->setUser($user);
                        $manager->persist($comment);
                    }
                }
                $manager->persist($article);
            }
            $manager->persist($user);
        }
        $manager->flush();
    }
}
