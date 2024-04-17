<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Location;
use App\Entity\EquipmentCategory;
use App\Entity\Equipment;
use App\Entity\Loan;
use App\Entity\LoanStatus;
use App\Entity\EquipmentSubCategory;
use App\Entity\EquipmentSubCategoryType;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $locations = [
            ["name" => "Studio", "roomNumber" => "H017"],
            ["name" => "H016", "roomNumber" => "H016"],
            ["name" => "H009", "roomNumber" => "H009"],
            ["name" => "H205", "roomNumber" => "H205"]
        ];

        $locationObjects = [];

        foreach ($locations as $location) {
            $locationObject = new Location();
            $locationObject->setName($location["name"]);
            $locationObject->setRoomNumber($location["roomNumber"]);
            array_push($locationObjects, $locationObject);
            $manager->persist($locationObject);
        }

        $equipmentCategories = [
            ["name" => "Audiovisuel", "slug" => "audiovisual", "description" => "Le matériel audiovisuel. Contient les appareils photos et caméras, objectifs, lumières, micros, trépieds, et divers accessoires."],
            ["name" => "VR", "slug" => "vr", "description" => "Les différents casques VR et autres emprunts liés à la VR."],
            ["name" => "Graphisme", "slug" => "graphic_design", "description" => "Tablettes graphiques et autres matériels pour la création numérique/traditionnelle."],
            ["name" => "Accessoires", "slug" => "accessories", "description" => "Le reste du matériel disponible à l'emprunt."],
            ["name" => "Autres", "slug" => "other", "description" => "Le reste du matériel disponible à l'emprunt."]
        ];

        $equipmentCategoryObjects = [];

        foreach ($equipmentCategories as $equipmentCategory) {
            $equipmentCategoryObject = new EquipmentCategory();
            $equipmentCategoryObject->setName($equipmentCategory["name"]);
            $equipmentCategoryObject->setSlug($equipmentCategory["slug"]);
            // $equipmentCategoryObject->setDescription($equipmentCategory["description"]);
            array_push($equipmentCategoryObjects, $equipmentCategoryObject);
            $manager->persist($equipmentCategoryObject);
        }

        $equipmentSubCategories = [
            ["name" => "Appareils photos", "formDisplayType" => EquipmentSubCategoryType::CARD, "slug" => "cameras"],
            ["name" => "Objectifs", "formDisplayType" => EquipmentSubCategoryType::LIST, "slug" => "lenses"],
            ["name" => "Micros", "formDisplayType" => EquipmentSubCategoryType::LIST, "slug" => "microphones"],
            ["name" => "Lumières", "formDisplayType" => EquipmentSubCategoryType::LIST, "slug" => "lights"],
            ["name" => "Trépieds", "formDisplayType" => EquipmentSubCategoryType::LIST, "slug" => "tripods"],
            ["name" => "Casques VR", "formDisplayType" => EquipmentSubCategoryType::LIST, "slug" => "vr_headsets"],
            ["name" => "Tablettes graphiques", "formDisplayType" => EquipmentSubCategoryType::CARD, "slug" => "pen_tablets"],
            ["name" => "Autres", "formDisplayType" => EquipmentSubCategoryType::LIST, "slug" => "other"],
            ["name" => "Batteries", "formDisplayType" => EquipmentSubCategoryType::LIST, "slug" => "batteries"]
        ];

        $equipmentSubCategoryObjects = [];

        foreach ($equipmentSubCategories as $equipmentSubCategory) {
            $equipmentSubCategoryObject = new EquipmentSubCategory();
            $equipmentSubCategoryObject->setName($equipmentSubCategory["name"]);
            $equipmentSubCategoryObject->setFormDisplayType($equipmentSubCategory["formDisplayType"]->value);
            $equipmentSubCategoryObject->setSlug($equipmentSubCategory["slug"]);
            array_push($equipmentSubCategoryObjects, $equipmentSubCategoryObject);
            $manager->persist($equipmentSubCategoryObject);
        }

        $equipment = [
            ["name" => "(#1) Canon 5D Mark IV", "description" => "Appareil photo Canon 5D Mark IV (+ Objectif 50mm f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#2) Canon 5D Mark IV", "description" => "Appareil photo Canon 5D Mark IV (+ Objectif 85mm f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#3) Canon R6", "description" => "Appareil photo Canon R6 (+ Objectif 50mm AF f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#4) Canon R6", "description" => "Appareil photo Canon R6 (+ Objectif 50mm AF f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#5) Canon R6 Mark II", "description" => "Appareil photo Canon R6 Mark II (+ Objectif 24-70mm)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#6) Canon R6 Mark II", "description" => "Appareil photo Canon R6 Mark II (+ Objectif 50mm AF f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#9) Canon 6D", "description" => "Appareil photo Canon 6D (+ Objectif 85mm f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#7) Canon 70D", "description" => "Appareil photo Canon 70D (+ Objectif 17-50mm f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#8) Canon 70D", "description" => "Appareil photo Canon 70D (+ Objectif 17-50mm f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#11) Blackmagic 4K", "description" => "Caméra Blackmagic 4K (+ Objectif 50mm f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#12) Blackmagic 4K", "description" => "Caméra Blackmagic 4K (+ Objectif 50mm f/1.5)", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#13) Camescope Sony 4K", "description" => "Camescope Sony 4K", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "(#14) Camescope Sony 4K", "description" => "Camescope Sony 4K", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "GoPro Hero 6 Black", "description" => "GoPro Hero 6 Black", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],
            ["name" => "GoPro Hero 10 Black", "description" => "GoPro Hero 10 Black", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[0], "location" => $locationObjects[0]],

            ["name" => "Obj. 14mm f/3.1 (EF)", "description" => "Objectif Canon 14mm f/3.1", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 24mm f/1.5 (EF)", "description" => "Objectif Canon 24mm f/1.5", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 35mm f/1.5 (EF)", "description" => "Objectif Canon 35mm f/1.5", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 50mm f/1.5 (EF)", "description" => "Objectif Canon 50mm f/1.5", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 85mm f/1.5 (EF)", "description" => "Objectif Canon 85mm f/1.5", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 100mm f/2.8 AF (EF)", "description" => "Objectif Canon 100mm f/2.4 Autofocus", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 24-105mm f/4 AF (EF)", "description" => "Objectif Canon 24-105mm f/4 Autofocus", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 70-200mm f/2.8 AF (EF)", "description" => "Objectif Canon 70-200mm f/2.8 Autofocus", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],
            ["name" => "Obj. 70-200mm f/2.8 AF (RF)", "description" => "Objectif Canon 70-200mm f/2.8 Autofocus", "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[1], "location" => $locationObjects[0], "type" => "EF"],

            ["name" => "Meta Quest 2", "description" => "Casque VR Meta Quest 2", "category" => $equipmentCategoryObjects[1], "subCategory" => $equipmentSubCategoryObjects[5], "quantity" => 17, "location" => $locationObjects[3]],
            ["name" => "Meta Quest 3", "description" => "Casque VR Meta Quest 3", "category" => $equipmentCategoryObjects[1], "subCategory" => $equipmentSubCategoryObjects[5], "quantity" => 1, "location" => $locationObjects[3]],
            ["name" => "HTC Vive Focus 3", "description" => "Casque VR HRC Vive Focus 3", "category" => $equipmentCategoryObjects[1], "subCategory" => $equipmentSubCategoryObjects[5], "quantity" => 2, "location" => $locationObjects[3]],
            ["name" => "Microsoft HoloLens 2", "description" => "Casque AR Microsoft HoloLens 2", "category" => $equipmentCategoryObjects[1], "subCategory" => $equipmentSubCategoryObjects[5], "quantity" => 3, "location" => $locationObjects[3], "loanable" => false],

            ["name" => "Wacom One", "description" => "Tablette graphique Wacom One", "category" => $equipmentCategoryObjects[2], "subCategory" => $equipmentSubCategoryObjects[6], "quantity" => 16, "location" => $locationObjects[1]],
            ["name" => "Wacom Intuos Medium", "description" => "Tablette graphique Intuos Medium", "category" => $equipmentCategoryObjects[2], "subCategory" => $equipmentSubCategoryObjects[6], "quantity" => 6, "location" => $locationObjects[1]],
            ["name" => "Wacom Cintiq", "description" => "Tablette graphique Wacom Cintiq (avec écran intégré)", "category" => $equipmentCategoryObjects[2], "subCategory" => $equipmentSubCategoryObjects[6], "quantity" => 2, "location" => $locationObjects[1]],
        
            ["name"=> "Trépied Manfrotto", "description" => "Trépied Manfrotto", "quantity" => 6, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[4], "showInTable" => false],
            ["name"=> "Trépied Miller", "description" => "Trépied Miller", "quantity" => 2, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[4], "showInTable" => false],
            ["name" => "Batterie Canon LP-E6NH", "description" => "Batterie supplémentaire pour Canon", "quantity" => 5, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[8], "showInTable" => false],
            ["name" => "Batterie Sony NP-F970", "description" => "Batterie supplémentaire pour Camescope Sony", "quantity" => 4, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[8], "showInTable" => false],
            ["name" => "Batterie Sony NP-FW50", "description" => "Batterie supplémentaire pour petits appareils Sony (et rail motorisé)", "quantity" => 5, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[8], "showInTable" => false],
            ["name" => "Batterie V-Mount", "description" => "Batterie V-Mount", "quantity" => 9, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[8], "showInTable" => false],
            ["name" => "Chargeur V-Mount", "description" => "Chargeur V-Mount", "quantity" => 3, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[8], "showInTable" => false],
            ["name" => "Filtre ND 63mm", "description" => "Filtre ND 63mm", "quantity" => 1, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[7], "showInTable" => false],
            ["name" => "Filtre ND 70mm", "description" => "Filtre ND 70mm", "quantity" => 1, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[7], "showInTable" => false],
            ["name" => "Bague conversion Sony E vers Canon EF", "description" => "Bague de conversion pour objectifs Sony E vers Canon EF", "quantity" => 1, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[7], "showInTable" => false], 
            ["name" => "Lecteur SSD", "description" => "Lecteur SSD pour Blackmagic", "quantity" => 1, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[7], "showInTable" => false],
            ["name" => "Malette Son", "description" => "Malette son avec Zoom H5 & Micro cravate", "quantity" => 12, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[2], "showInTable" => false],
            ["name" => "Perche", "description" => "Perche pour micro Rode NTG-2", "quantity" => 2, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[2], "showInTable" => false],
            ["name" => "Fresnel LED 60W", "description" => "Fresnel LED 60W", "quantity" => 6, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[3], "showInTable" => false],
            ["name" => "Fresnel LED Bi-Color 60W", "description" => "Fresnel LED Bi-Color 60W", "quantity" => 2, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[3], "showInTable" => false],
            ["name" => "Panneau LED", "description" => "Panneau LED", "quantity" => 6, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[3], "showInTable" => false],
            ["name" => "Tubes Nanlite", "description" => "Tubes RGB Nanlite", "quantity" => 1, "location" => $locationObjects[0], "category" => $equipmentCategoryObjects[0], "subCategory" => $equipmentSubCategoryObjects[3], "showInTable" => true]
        ];

        $equipmentObjects = [];

        foreach ($equipment as $equipment) {
            $equipmentObject = new Equipment();
            $equipmentObject->setName($equipment["name"]);
            $equipmentObject->setDescription($equipment["description"]);
            $equipmentObject->setCategory($equipment["category"]);
            $equipmentObject->setSubCategory($equipment["subCategory"]);
            $equipmentObject->setType($equipment["type"]??null);
            $equipmentObject->setLocation($equipment["location"]);
            $equipmentObject->setLoanable($equipment["loanable"]??true);
            $equipmentObject->setQuantity($equipment["quantity"]??1);
            $equipmentObject->setShowInTable($equipment["showInTable"]??true);
            array_push($equipmentObjects, $equipmentObject);
            $manager->persist($equipmentObject);
        }

        $users = [
            ["email" => "abcd@ab.cd", "roles" => ["ROLE_ADMIN"], "name" => "Admin McAdminface", "password" => "abcd"],
            ["email" => "john@ab.cd", "roles" => ["ROLE_USER"], "name" => "John Mcstudentface", "password" => "abcd"]
        ];

        $userObjects = [];

        foreach ($users as $user) {
            $userObject = new User();
            $userObject->setEmail($user["email"]);
            $userObject->setRoles($user["roles"]);
            $userObject->setName($user["name"]);
            $userObject->setActive(true);
            $userObject->setPassword($this->hasher->hashPassword($userObject, $user["password"]));
            array_push($userObjects, $userObject);
            $manager->persist($userObject);
        }

        $loans = [
            ["loanDate" => new \DateTime("+2 day 17:00"), "returnDate" => new \DateTime("+5 day 17:00"), "loanStatus" => LoanStatus::PENDING, "equipmentLoaned" => [$equipmentObjects[0], $equipmentObjects[13], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[0]],
            ["loanDate" => new \DateTime("+12 day 17:00"), "returnDate" => new \DateTime("+13 day 17:00"), "loanStatus" => LoanStatus::PENDING, "equipmentLoaned" => [$equipmentObjects[0], $equipmentObjects[13], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[0]],
            ["loanDate" => new \DateTime("+1 day 17:00"), "returnDate" => new \DateTime("+5 day 17:00"), "loanStatus" => LoanStatus::PENDING, "equipmentLoaned" => [$equipmentObjects[1], $equipmentObjects[8], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[0]],
            ["loanDate" => new \DateTime("+11 day 17:00"), "returnDate" => new \DateTime("+14 day 17:00"), "loanStatus" => LoanStatus::PENDING, "equipmentLoaned" => [$equipmentObjects[1], $equipmentObjects[8], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[0]],
            ["loanDate" => new \DateTime("+3 day 17:00"), "returnDate" => new \DateTime("+5 day 17:00"), "loanStatus" => LoanStatus::PENDING, "equipmentLoaned" => [$equipmentObjects[2], $equipmentObjects[9], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[0]],
            ["loanDate" => new \DateTime("+13 day 17:00"), "returnDate" => new \DateTime("+15 day 17:00"), "loanStatus" => LoanStatus::PENDING, "equipmentLoaned" => [$equipmentObjects[2], $equipmentObjects[9], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[0]],
            ["loanDate" => new \DateTime("+4 day 17:00"), "returnDate" => new \DateTime("+5 day 17:00"), "loanStatus" => LoanStatus::PENDING, "equipmentLoaned" => [$equipmentObjects[3], $equipmentObjects[10], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[1]],
            ["loanDate" => new \DateTime("+12 day 17:00"), "returnDate" => new \DateTime("+15 day 17:00"), "loanStatus" => LoanStatus::ACCEPTED, "equipmentLoaned" => [$equipmentObjects[3], $equipmentObjects[10], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[1]],
            ["loanDate" => new \DateTime("+2 day 17:00"), "returnDate" => new \DateTime("+5 day 17:00"), "loanStatus" => LoanStatus::ACCEPTED, "equipmentLoaned" => [$equipmentObjects[4], $equipmentObjects[11], $equipmentObjects[44], $equipmentObjects[46]], "loaner" => $userObjects[1]],
            ["loanDate" => new \DateTime("+12 day 17:00"), "returnDate" => new \DateTime("+14 day 17:00"), "loanStatus" => LoanStatus::ACCEPTED, "equipmentLoaned" => [$equipmentObjects[4], $equipmentObjects[11], $equipmentObjects[45], $equipmentObjects[47]], "loaner" => $userObjects[1]],
            ["loanDate" => new \DateTime("+2 day 17:00"), "returnDate" => new \DateTime("+5 day 17:00"), "loanStatus" => LoanStatus::ACCEPTED, "equipmentLoaned" => [$equipmentObjects[6], $equipmentObjects[12], $equipmentObjects[46], $equipmentObjects[50], $equipmentObjects[51]], "loaner" => $userObjects[1]],
            ["loanDate" => new \DateTime("+12 day 17:00"), "returnDate" => new \DateTime("+14 day 17:00"), "loanStatus" => LoanStatus::ACCEPTED, "equipmentLoaned" => [$equipmentObjects[6], $equipmentObjects[12], $equipmentObjects[46], $equipmentObjects[50], $equipmentObjects[51]], "loaner" => $userObjects[1]],
        ];

        foreach ($loans as $loan) {
            $loanObject = new Loan();
            $loanObject->setDepartureDate($loan["loanDate"]);
            $loanObject->setReturnDate($loan["returnDate"]);
            $loanObject->setStatus($loan["loanStatus"]->value);
            $loanObject->setLoaner($loan["loaner"]);
            foreach ($loan["equipmentLoaned"] as $equipmentLoaned) {
                $loanObject->addEquipmentLoaned($equipmentLoaned);
            }
            $manager->persist($loanObject);
        }

        $manager->flush();
    }
}
