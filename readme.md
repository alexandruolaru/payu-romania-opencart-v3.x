# PayU Romania Module for Opencart v3.x using the PHP PayU LiveUpdate class from 2018

![PayU](./upload/admin/view/image/payment/payu.png)

## INSTALLATION
************************************************************************
1) Copy the files from the "upload" folder to the directory where your online store is located.
************************************************************************
2) Access the PayU control panel, and from the "Account Settings" section, you can obtain the merchant code and secret key, which you can configure in the OpenCart module.
************************************************************************
3) In the PayU control panel, in the "Account Management" section, select "Account Settings." Check in the "Notifications" section if the "Instant Payment Notification (IPN)" radio button is selected, and for the "Send notifications for" option, check only the "Authorized orders" option. Update the settings.
************************************************************************
4) Click on the second tab "IPN Settings." Enter the address for the callback function http://yourstore.com/index.php?route=extension/payment/payu/ipn. Save the settings.

##### Note
* Tested up to PHP 8.0X
* Tested up to OpenCart v 3.0.3.8