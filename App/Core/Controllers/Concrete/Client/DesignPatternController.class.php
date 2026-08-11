<?php

declare(strict_types=1);
class DesignPatternController extends Controller
{
    private AbstractFactory $abstractFactory;

    public function __construct(AbstractFactory $abstractFactory)
    {
        $this->abstractFactory = $abstractFactory;
    }

    public function index() : Response
    {
        //product1
        $pdt1 = $this->abstractFactory->createProduct1();
        $pdt2 = $this->abstractFactory->createProduct2();
        $fabriques = [
            new FabriqueVehiculeElectricite(),
            new FabriqueVehiculeEssence(),
        ];
        $couleurs = ['noir', 'rouge', 'blanc'];
        $marques = ['Suzuki', 'Peugeot', 'BMW'];

        $vehicules = [];

        foreach ($fabriques as $fabrique) {
            $catalogue = new Catalogue($fabrique);

            $vehicules[] = $catalogue->creerAutomobile(
                $marques[array_rand($marques)],
                $couleurs[array_rand($couleurs)],
                mt_rand(110, 150),
                3.2
            );

            $vehicules[] = $catalogue->creerScooter(
                $marques[array_rand($marques)],
                $couleurs[array_rand($couleurs)],
                mt_rand(15, 90)
            );
        }
        $display = [
            'product1' => $pdt1->display(),
            'product2' => $pdt2->display(),
            'memory' => memory_get_usage(),
            'vehicules' => $vehicules,
        ];
        return $this->response
            ->setContent($this->render('designPattern/index', $display));
    }

    public function builder() : Response
    {
        $constructeursLiasse = [
            new ConstructeurLiasseVehiculeHtml(),
            new ConstructeurLiasseVehiculePdf(),
        ];
        $liasses = '';
        foreach ($constructeursLiasse as $constructeurLiasse) {
            $vendeur = new Vendeur($constructeurLiasse);
            $liasse = $vendeur->construit('Ferrandez');
            $liasses .= $liasse->imprime() . "\n";
        }
        return $this->response
            ->setContent($this->render('designPattern/builder', ['liasse' => $liasses]));
    }

    public function factoryMethod() : Response
    {
        $c1 = '';
        $client = new ClientComptant();
        $c1 .= $client->nouvelleCommande(2000);
        $c1 .= $client->nouvelleCommande(10000);

        $c2 = '';
        $client = new ClientCredit();
        $c2 .= $client->nouvelleCommande(2199.99);
        $c2 .= $client->nouvelleCommande(10000);
        return $this->response
            ->setContent($this->render('designPattern/factoryMethod', [
                'client1' => $c1, 'client2' => $c2]));
    }

    public function prototype() : Response
    {
        $liasseVierge = LiasseVierge::getInstance();

        $liasseVierge->ajoute(new BonDeCommande());
        $liasseVierge->ajoute(new CertificatCession());
        $liasseVierge->ajoute(new DemandeImmatriculation());

        $liasseClient = new LiasseClient('Georges Durand');
        $r = $liasseClient->affiche();
        $r = $liasseClient->imprime();
        return $this->response
            ->setContent($this->render('designPattern/prototype', [
                'affiche' => $liasseClient->affiche(),
                'imprime' => $liasseClient->imprime()]));
    }
}