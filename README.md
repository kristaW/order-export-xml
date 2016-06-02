# Order to XML on save

This module will write copy of order to /var/blueacorn/{date}/{orderid}.xml when customer is submitting his/hers order.

## How
The xml notation is read from config.xml file in the module. 

```
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
```

Data is collected observering event sales_order_place_after and looping trought it.
The xml layout will not affect the file construction. They can be as deep as needed with any tags.
Xml construction is only interested information inside the xml tags.

### Syntax:
- billing.	// will collect information from order->addresses->billing address info.
- order.
- shipping.	// from order->addresses->shipping address info.


This module relies hevily of **Varien_Filter_Template** modules functionality.
Each element needs to bee surrounded  with double angle brackets 

#### Example:
{{var order.entity_id}} 
{{var billing.firstname}}

### Order Items:
are looped trough using attribute 'foreach' with value 'items'. See example below.
The foreach tells the code to loop trough the result values (expecting it to be an array)
and adds new node with it's children to each item. Foreach attribute value is being used as an keyword 
to know what is being looped trough. Currently we only have 'items' keyword.

The foreach attribute is removed from result xml.

### Example:
```
<blueacorn>
  <export>
    <order>
      <test> {{var order.entity_id}} </test>
      <address_billing>{{var billing.firstname}} {{var billing.lastname}}</address_billing>
      <address>{{var shipping.firstname}} {{var shipping.lastname}}</address>
      <items>
        <item foreach="items">
          <sku>{{var sku}}</sku>
          <name>{{var name}}</name>
        </item>
       </items>
    </order>
  </export>
</blueacorn>
```

## Known Issues:
- doesn't have proper handling for typing errors occurring in config.xml
