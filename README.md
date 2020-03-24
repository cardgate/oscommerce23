![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module for osCommerce 2.3

[![Build Status](https://travis-ci.org/cardgate/oscommerce23.svg?branch=master)](https://travis-ci.org/cardgate/oscommerce23)

## Support

This plugin supports osCommerce version **2.3.x** .

## Preparation

The usage of this module requires that you have obtained CardGate security credentials.  
Please visit [My CardGate](https://my.cardgate.com/) and retrieve your credentials, or contact your accountmanager.

## Installation

1. Download and unzip the most recent [cardgate.zip](https://github.com/cardgate/oscommerce23/releases/) file on your desktop.

2. Upload the **contents** of the **cardgate** folder, to the **root** folder of your shop.


## Configuration

1. Before the **CardGate administration** will be visible, the **admin/includes/column_left.php** 
   file of osCommerce needs to be modified.  
   Line 23 is: **include(DIR_WS_BOXES . 'tools.php');**  
   Add the following line of code below this line:  
   **include(DIR_WS_BOXES . 'cgp_orders.php');**  
   (Please note the **semicolon** at the end of the line!)  
   
2. Go to the **admin section** of your webshop, and on the left, select **Modules, Payment**.

3. On the right, click on **Install Module**, and select the payment module you wish to activate.  
   (All CardGate modules have **Card Gate Plus** written behind the name of the payment method.  
   On the right click on **Install Module**.  
   
4. On the right, click on the **Edit** button of the installed payment module.

5. Select **true** to activate the payment module.

6. Now enter the **site ID**, and the **hash key** which you can find at **Sites** on My CardGate. 

7. Enter the default **gateway language**, for example **en** for English, or **nl** for Dutch.

8. Select the **payment zone** if you wish to restrict the use of this module to a particular zone.

9. Select **none** when the payment method must be visible to all clients.
   
10. Set the **sort order** and **payment statuses** or use the default values.

11. Click on **Save** when all settings are done.

12. Repeat steps **3 to 11** for all the desired payment methods.

13. Go to [My CardGate](https://my.cardgate.com/), choose **Sites** and select the appropriate site.

14. Go to **Connection to the website** and enter the **Callback URL**, for example:  
    **http://mywebshop.com/ext/modules/payment/cgp/cgp.php**  
    (Replace **http://mywebshop.com** with the URL of your webshop.)  
    
15. When you are **finished testing** make sure that you switch **all activated payment methods** from **Test Mode**  
    to **Live mode** and save it (**Save**).
    
## Requirements

No further requirements.
