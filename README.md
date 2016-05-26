This module will write copy of order to /var/blueacorn/{date}/{orderid}.xml when customer is submitting his/hers order.

The xml notation is read from config.xml file in the module. 

<config>
  <global>
    ...
  </global>
  ...
  <blueacorn>
    <export>
      <order>
        {data that wish to be implemented in copy of order}
      </order>
    </export>
  </blueacorn>
</config>


Data is collected observering event sales_order_place_after and looping trought it.
The xml layout will not affect the file construction. They can be as deep as needed with any tags.
Xml construction is only interested information inside the xml tags.

Syntax:
- {object variables}
- billing.	// will collect information from order->addresses->billing address info.
- shipping.	// from order->addresses->shipping address info.
- items.	// currently not working properly but eventually will collect order items.

Example:

<blueacorn>
  <export>
    <order>
       <status>status</status>
       <firstname>customer_firstname</firstname>
       <lastname>customer_lastname</lastname>
       <email>customer_email</email>
       <billing>
         <city>billing.city</city>
         <zip>billing.postcode</zip>
         <address>billing.street</address>
         <phone>billing.telephone</phone>
           <shipping>
             <receiper>shipping.firstname</receiper>
           </shipping>
       </billing>
    </order>
  </export>
</blueacorn>


Known Issues:
- items will break redirecting
- items will add requested information of bought item on first, but will overwrite it after that.
- not valid xml (missing xml starting tags)
- doesn't have proper handling for typing errors occurring in config.xml
