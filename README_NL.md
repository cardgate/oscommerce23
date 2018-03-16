![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module voor osCommerce 2.3

[![Total Downloads](https://img.shields.io/packagist/dt/cardgate/oscommerce23.svg)](https://packagist.org/packages/cardgate/oscommerce23)
[![Latest Version](https://img.shields.io/packagist/v/cardgate/oscommerce23.svg)](https://github.com/cardgate/oscommerce23/releases)
[![Build Status](https://travis-ci.org/cardgate/oscommerce23.svg?branch=master)](https://travis-ci.org/cardgate/oscommerce23)

## Support

Deze plug-in is geschikt voor osCommerce versie **2.3.x**.

## Voorbereiding

Voor het gebruik van deze module zijn CardGate gegevens nodig
Bezoek hiervoor [Mijn CardGate](https://my.cardgate.com/) en haal je Site ID and Hash Key op  
of neem contact op met je accountmanager.

## Installatie

1. Download en unzip het **catalog.zip** bestand op je bureaublad.

2. Upload **alle mappen en bestanden** in de **catalog map** naar de **root map** van je webshop.


## Configuratie

1. Voordat de **CardGate administration** zichtbaar wordt moet het  
   **admin/includes/column_left.php** bestand van osCommerce aangepast worden.  
   Op regel 23 staat: **include(DIR_WS_BOXES . 'tools.php');**  
   Voeg onder deze regel de volgende regel code toe:  
   include(DIR_WS_BOXES . 'cgp_orders.php');  
   (Let op de **puntkomma** aan het einde van de regel!)  
   
2. Ga naar het **admin gedeelte** van je webshop en selecteer aan de linkerkant **Modules, Betaling**.

3. Klik rechts op **Installeer Module** en selecteer de betaalmodule die je wenst te activeren.  
   (Alle CardGate modules hebben **Card Gate Plus** achter de naam van de betaalmethode staan)  
   Klik nu rechts op **Installeer Module**.  
   
4. Klik rechts op de **Aanpassen** knop van de geïnstalleerde betaalmodule.

5. Selecteer **true** om de betaalmodule te activeren.

6. Vul nu de **Site ID** en de **Hash Key (Codeersleutel)** in, deze kun je vinden bij **Sites**  
   op [Mijn CardGate](https://my.cardgate.com/).  

7. Vul de standaard **gateway taal** in, bijvoorbeeld **en** voor Engels of **nl** voor Nederlands.

8. Selecteer de **payment zone** als je het gebruik van deze module wilt beperken tot een bepaalde zone.

9. Selecteer **geen** wanneer de betaalmethode zichtbaar moet zijn voor alle klanten.
   
10. Vul de **sorteer volgorde** en **betaal statussen** in of gebruik de standaard waarden.

11. Klik op **Bewaren** wanneer alle instellingen gedaan zijn.

12. Herhaal de **stappen 3 tot 11** voor alle gewenste betaalmethoden.

13. Ga naar [Mijn CardGate](https://my.cardgate.com/), kies **Sites** en selecteer de juiste site.

14. Vul bij **Technische koppeling** de **Callback URL** in, bijvoorbeeld:  
    **http://www.mijnwebshop.com/ext/modules/payment/cgp/cgp.php**
    (Vervang **http://mijnwebshop.com** met de URL van je webshop)
    
15. Zorg ervoor dat je na het testen **alle geactiveerde betaalmethoden** omschakelt van **Test mode** naar  
    **Live mode** en sla het op (**Bewaren**).  
    
## Vereisten

Geen verdere vereisten.
