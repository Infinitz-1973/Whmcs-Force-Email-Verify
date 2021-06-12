<?php
if (!defined("WHMCS"))
die("Can't access the file directly!");

use WHMCS\View\Menu\Item as MenuItem;
use Illuminate\Database\Capsule\Manager as Capsule;

# Would you like to prevent unverified accounts from placing orders ?, set it to false to accept orders
define("PREVENTUNVERIFIEDORDERS", true);
# How many days to wait before deactivating the unverified account, set 0 to deactivate this feature
define("DEACTIVATEACCOUNTAFTERXDAYS", 5);
# How many days to wait before setting the unverified account as closed, set 0 to disable this feature
define("CLOSEACCOUNTAFTERXDAYS", 7);

# Orders will not be completed if the email is not verified.
add_hook("ShoppingCartValidateCheckout", 1, function($vars){
    if (PREVENTUNVERIFIEDORDERS===true){
        // get the client data
        $client = Menu::context("client");
        // verifies if the client is logged in and if it is found
         if (!is_null($client) && $client) {
             // check if the email is not verified
            if ($client->isEmailAddressVerified()==false)
            {
                // message
                return array("<b>You must first verify your email address before completing any order</b>");
            }
         }
    }
});

# Deactivate unverified account after x days
add_hook("DailyCronJob", 1, function($vars){
    if (intval(DEACTIVATEACCOUNTAFTERXDAYS)!==0){
        $dateCreated = date("Y-m-d", strtotime("now - ".intval(DEACTIVATEACCOUNTAFTERXDAYS)." days"));
        $getAccounts = Capsule::table("tblclients")->where("datecreated", "=", $dateCreated)->where("email_verified", "=", 0);
        foreach ($getAccounts->get() as $account){
            Capsule::table("tblclients")->where("id", $account->id)->update(array("status" => "Inactive"));
        }
    }
});

# Close unverified accounts after X days
add_hook("DailyCronJob", 1, function($vars){
    if (intval(CLOSEACCOUNTAFTERXDAYS)!==0){
        $dateCreated = date("Y-m-d", strtotime("now - ".intval(CLOSEACCOUNTAFTERXDAYS)." days"));
        $getAccounts = Capsule::table("tblclients")->where("datecreated", "=", $dateCreated)->where("email_verified", "=", 0);
        foreach ($getAccounts->get() as $account){
            Capsule::table("tblclients")->where("id", $account->id)->update(array("status" => "Closed"));
        }
    }
});