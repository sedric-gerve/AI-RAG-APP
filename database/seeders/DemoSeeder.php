<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Compte Démo', 'password' => 'demo12345']
        );

        $products = collect([
            ['name' => 'Site vitrine', 'sku' => 'SRV-SITE', 'unit_price' => 1200, 'description' => 'Conception et développement d\'un site vitrine sur mesure.'],
            ['name' => 'Application mobile', 'sku' => 'SRV-APP', 'unit_price' => 4500, 'description' => 'Développement d\'une application mobile iOS/Android.'],
            ['name' => 'Maintenance mensuelle', 'sku' => 'SRV-MAINT', 'unit_price' => 150, 'description' => 'Forfait de maintenance et petites évolutions.'],
            ['name' => 'Audit technique', 'sku' => 'SRV-AUDIT', 'unit_price' => 800, 'description' => 'Audit de sécurité et de performance.'],
            ['name' => 'Hébergement annuel', 'sku' => 'SRV-HOST', 'unit_price' => 300, 'description' => 'Hébergement et nom de domaine pour un an.'],
        ])->map(fn (array $data) => Product::updateOrCreate(['sku' => $data['sku']], $data));

        $siteVitrine = $products->firstWhere('sku', 'SRV-SITE');
        $hebergement = $products->firstWhere('sku', 'SRV-HOST');
        $maintenance = $products->firstWhere('sku', 'SRV-MAINT');

        $lemoine = Client::updateOrCreate(
            ['name' => 'Boulangerie Lemoine'],
            [
                'email' => 'contact@boulangerie-lemoine.fr',
                'phone' => '02 41 55 12 34',
                'website' => 'https://boulangerie-lemoine.fr',
                'city' => 'Angers',
                'country' => 'France',
                'notes' => 'Cliente historique, très réactive.',
            ]
        );
        Contact::updateOrCreate(
            ['client_id' => $lemoine->id, 'email' => 'marie.lemoine@boulangerie-lemoine.fr'],
            ['first_name' => 'Marie', 'last_name' => 'Lemoine', 'phone' => '06 12 34 56 78', 'position' => 'Gérante']
        );
        $oppLemoine = Opportunity::updateOrCreate(
            ['client_id' => $lemoine->id, 'title' => 'Refonte du site vitrine'],
            ['amount' => 1200, 'stage' => 'negociation', 'probability' => 70, 'expected_close_date' => now()->addWeeks(2)]
        );
        $orderLemoine = Order::updateOrCreate(
            ['client_id' => $lemoine->id, 'opportunity_id' => $oppLemoine->id],
            ['status' => 'confirmee', 'ordered_at' => now()->subDays(5)]
        );
        OrderItem::updateOrCreate(
            ['order_id' => $orderLemoine->id, 'product_id' => $siteVitrine->id],
            ['description' => $siteVitrine->name, 'quantity' => 1, 'unit_price' => $siteVitrine->unit_price]
        );
        OrderItem::updateOrCreate(
            ['order_id' => $orderLemoine->id, 'product_id' => $hebergement->id],
            ['description' => $hebergement->name, 'quantity' => 1, 'unit_price' => $hebergement->unit_price]
        );

        $dubois = Client::updateOrCreate(
            ['name' => 'Cabinet Dubois & Associés'],
            [
                'email' => 'contact@dubois-avocats.fr',
                'phone' => '01 42 33 44 55',
                'city' => 'Paris',
                'country' => 'France',
                'notes' => 'Cabinet d\'avocats en droit des affaires, 12 collaborateurs.',
            ]
        );
        Contact::updateOrCreate(
            ['client_id' => $dubois->id, 'email' => 'jean.dubois@dubois-avocats.fr'],
            ['first_name' => 'Jean', 'last_name' => 'Dubois', 'phone' => '06 98 76 54 32', 'position' => 'Avocat associé']
        );
        Opportunity::updateOrCreate(
            ['client_id' => $dubois->id, 'title' => 'Application de gestion de dossiers'],
            ['amount' => 4500, 'stage' => 'proposition', 'probability' => 50, 'expected_close_date' => now()->addMonth()]
        );

        $fitness = Client::updateOrCreate(
            ['name' => 'Fitness Club Énergie'],
            [
                'email' => 'contact@fitness-energie.fr',
                'phone' => '04 78 22 11 00',
                'city' => 'Lyon',
                'country' => 'France',
            ]
        );
        Contact::updateOrCreate(
            ['client_id' => $fitness->id, 'email' => 'sophie.martin@fitness-energie.fr'],
            ['first_name' => 'Sophie', 'last_name' => 'Martin', 'phone' => '06 11 22 33 44', 'position' => 'Directrice']
        );
        $oppFitness = Opportunity::updateOrCreate(
            ['client_id' => $fitness->id, 'title' => 'Maintenance et évolutions du site'],
            ['amount' => 150, 'stage' => 'gagnee', 'probability' => 100, 'expected_close_date' => now()->subWeek()]
        );
        $orderFitness = Order::updateOrCreate(
            ['client_id' => $fitness->id, 'opportunity_id' => $oppFitness->id],
            ['status' => 'facturee', 'ordered_at' => now()->subWeek()]
        );
        OrderItem::updateOrCreate(
            ['order_id' => $orderFitness->id, 'product_id' => $maintenance->id],
            ['description' => $maintenance->name.' (3 mois)', 'quantity' => 3, 'unit_price' => $maintenance->unit_price]
        );

        $techno = Client::updateOrCreate(
            ['name' => 'Techno Solutions SARL'],
            [
                'email' => 'contact@techno-solutions.fr',
                'phone' => '05 61 20 30 40',
                'city' => 'Toulouse',
                'country' => 'France',
            ]
        );
        Contact::updateOrCreate(
            ['client_id' => $techno->id, 'email' => 'marc.petit@techno-solutions.fr'],
            ['first_name' => 'Marc', 'last_name' => 'Petit', 'phone' => '06 55 44 33 22', 'position' => 'CTO']
        );
        Opportunity::updateOrCreate(
            ['client_id' => $techno->id, 'title' => 'Audit sécurité et performance'],
            ['amount' => 800, 'stage' => 'qualification', 'probability' => 30, 'expected_close_date' => now()->addWeeks(3)]
        );

        $epicerie = Client::updateOrCreate(
            ['name' => 'Épicerie Bio Verte'],
            [
                'email' => 'contact@epicerie-bio-verte.fr',
                'phone' => '03 88 12 45 67',
                'city' => 'Strasbourg',
                'country' => 'France',
            ]
        );
        Contact::updateOrCreate(
            ['client_id' => $epicerie->id, 'email' => 'claire.rousseau@epicerie-bio-verte.fr'],
            ['first_name' => 'Claire', 'last_name' => 'Rousseau', 'phone' => '06 77 88 99 00', 'position' => 'Propriétaire']
        );
        Opportunity::updateOrCreate(
            ['client_id' => $epicerie->id, 'title' => 'Site vitrine avec boutique en ligne'],
            [
                'amount' => 2000,
                'stage' => 'perdue',
                'probability' => 0,
                'expected_close_date' => now()->subWeeks(2),
                'notes' => 'Partie chez un concurrent proposant un tarif plus bas.',
            ]
        );
    }
}
