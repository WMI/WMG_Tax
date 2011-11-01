# WMG_Tax - Gross pricing that *works* #

The WMG_Tax module addresses an issue within Magento whereby Magento is unable to correctly apply tax rules for different tax rates to product prices including tax (gross) without the gross price changing upon the tax rate changing

It is common within Europe for prices to include tax, and for a vendor to want to preserve "nice pricing" (eg ending in .99). In order to preserve nice pricing where multiple tax rates exist, the vendor can only achieve this by flexing both the taxable element AND the net price of the product. In some instances, this will mean the vendor will make less profit selling to customers resident where there is a higher tax rate, but this is often an acceptable drawback in order to preserve uniform pricing 

Without the WMG_Tax module, it's currently not possible to preserve nice pricing and to serve multiple tax rates within Magento

## Why multiple tax rates? ##

Within Europe, once a vendor has shipped value of goods greater than a specific threshold (generally, equivalent to ~€100,000) into an EU member state (within 12 months) they are required to register for VAT within that EU member state. At this point, the vendor must then charge VAT to customers who have thier goods shipped to this member state at the correct VAT rate for the member state. For any other EU member state where the vendor is *NOT* VAT registered, they must continue to charge VAT at the rate of the member state in which the vendor is based

An example would be if a vendor is based in UK, which currently has a VAT rate of 20%. This vendor would charge 20% VAT to *ALL* EU member customers until reaching the VAT registration threshold of another EU member state (say, Italy). At which point, the vendor would be required to register for VAT in Italy, and charge their Italian customers at the current Italian VAT rate, which is 21%

In the above example, (with Gross pricing) if a product costs 9.99 within Magento the cost to *EVERYBODY* except customers in Italy would be 9.99, however, for those customers in Italy, the price would be 9.67

This is because of the incorrect way in which Magento calculates gross prices. First, it deducts the default tax from the gross price - in this case, it deducts 20% (or 1.998) to arrive at what Magento believes to be the NET price (7.992). Magento then proceeds to ADD the new tax rate (7.992 * 0.21 = 1.67832) to the newly derived net figure, which gives us 7.992 + 1.67832 = 9.67.  

Although our tax rate is higher, it's cost the customer *LESS*. This is obviously wrong - what we want to achieve here is that that gross price remains at 9.99, and the tax element within that 9.99 changes accordingly

Eg, for our UK customers, the tax element would be 1.998, and for our Italian customers, the tax element would be 2.097 - a different of 0.10, not a difference of 0.22

## How does WMG_Tax work? ##

Pretty simply, really

WMG_Tax is a small code fix which corrects the calculation above, however, it also requires that specific tax configuration is made within the Magento backend. This configuration is detailed within the installation section

## Installation ##

Firstly, you need to configure your tax rules within Magento correctly

To do this, we must first ensure that our default tax calculation is to apply 0% tax. This ensures that when a product is added to a customers cart, that no "default" tax is assumed, and so that there is no tax element displayed to the customer until they have chosen their shipping destination during checkout

This is achieved by first selecting a country where you are not registered to charge taxes as your default store location. Make sure that you are in the default store scope, then Go to System > Configuration > General and set the value for "Default Country". For most European organisations, it would be safe to choose "United States" here

Now make sure that all of your websites and storeviews are set to use the global default

Next, in System > Configuration > Sales > Tax be sure that your "Tax Calculation Based On" is set to "Shipping Destination" and that "Catalog Prices" is set to "Including Tax". In the Default Tax Destination Calculation, the "Default Country" should be "None" (at the top of the dropdown menu). Make sure that those settings have been applied to all websites and storeviews

Now you need to copy the code from this repository into your Magento instance - copy the XML file from etc/modules into your Magento installation's app/etc/modules directory. Copy the contents of repository's app/code/local into your Magento installation's app/code/local directory

Be sure to clear your cache, and everything should now be working

## How do I check it's working? ##

The most immediate indication that the module is working as expected is that after adding an item to your cart, you'll no longer see any tax breakdown in the cart summary. You won't see any tax breakdown at all until the final confirmation step of the checkout

You should also notice that on the final stage of checkout, the gross price for the item has not changed if you go back to the shipping information step and change to a different EU member state in which you're VAT registered. It's likely the grand total will change (because shipping costs will likely change) but the gross price for the items will remain the same 

## NOTES ##

Due to well known issues with 2 digit rounding precision in some versions of Magento, it's entirely possible that the gross price WILL change by +/- 0.01. Unfortunately, there's not much that can be done about that - other than applying various patches that change the rounding precision to 4 digits

## Compatibility ##

At the moment, this module has only been verified to work on Magento Enterprise 1.9 - it's possible that it will work on other Magento versions, however, initial indications after looking at Magento CE 1.6.1.0 are that it will not work without modification on the Magento 1.6 CE series. As Magento CE and Magento PE are investigated, we'll add branches into the repository specific for each version of Magento  
