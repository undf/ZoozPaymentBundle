ZoozPaymentBundle
=================

Integration of the Zooz payment platform in your Symfony2&amp;AngularJS project.

This bundle assumes you have installed AngularJs in your project.

#Installation

###Step 1: Download the bundle

```
> composer require undf/zooz-payment-bundle dev-master
```

###Step2: Enable the bundle
```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Undf\ZoozPaymentBundle\UndfZoozPaymentBundle(),
    );
}
```

#Configuration
```yml
# app/config.yml

undf_zooz_payment:
  config:
    unique_id: <your Zooz application unique id>
    app_key: <your Zoop application app key>
    sandbox_mode: true #Enable/disable the Zooz server sandbox mode

  ajax_mode: true #If true, Zooz server transaction response will be handled from client side

  payment:
    entity: YourBundle:Payment #Your payment entity class
    manager: your.manager.service.id #Service id of your payment manager class

  templates: #These templates are only used in non-ajax mode
    return: UndfZoozPaymentBundle:Transaction:return.html.twig #Succeed payment template
    cancel: UndfZoozPaymentBundle:Transaction:cancel.html.twig #Failed payment template
```

```yml
# app/routing.yml

undf_zooz_payment:
    resource: "@UndfZoozPaymentBundle/Controller/"
    type:     annotation
    prefix:   /

```

#Use
##Server side
####Step 1: Create your Payment entity
```php
namespace Your\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Undf\ZoozPaymentBundle\Entity\BasePayment;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class Payment extends BasePayment
{
  ...
}
```

Note: Alternatively, you can directly implement the interface Undf\ZoozPaymentBundle\Model\PaymentInterface.

####Step 2: Create your InvoiceItem entity (optional)
As an option, the bundle allows you to configure a list of items to be included in the invoice
which is shown on the Zooz window (only for Paypal payments). If you want to do so, you need to create
an InvoiceItem entity:
```php
namespace Your\Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Undf\ZoozPaymentBundle\Entity\BaseInvoiceItem;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class InvoiceItem extends BaseInvoiceItem
{
  ...
}
```
Note: Alternatively, you can directly implement the interface Undf\ZoozPaymentBundle\Model\InvoiceItemInterface.

And you also need to overwrite the "getInvoiceItemCollection" method in your Payment entity class:
```php

class Payment extends BasePayment
{
    ...
    public function getInvoiceItemCollection()
    {
        //Return the entity property holding your item collection, where every item
        //must implement Undf\ZoozPaymentBundle\Model\InvoiceItemInterface
        return $this->collection;
    }
    ...
}
```

####Step 3: Create your Payment manager class

Make sure your manager class implements the Undf\ZoozPaymentBundle\Model\PaymentManagerInterface.

```php
namespace Your\Bundle\Manager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Undf\ZoozPaymentBundle\Model\PaymentManagerInterface;
use Undf\ZoozPaymentBundle\Model\PaymentInterface;

class PaymentManager implements PaymentManagerInterface
{
    protected $em;

    public function __construct(RegistryInterface $doctrine)
    {
      $this->em = $doctrine->getManagerForClass('YourBundle:Payment');
    }

    public function update(PaymentInterface $payment)
    {
      $this->em->persist($payment);
      $this->em->flush();
    }

    public function handlePaymentSuccess(PaymentInterface $payment)
    {
        ...
    }

    public function handlePaymentFail(PaymentInterface $payment)
    {
        ...
    }

}
```

Adds your manager class to the service container
```
<service id="your.manager.service.id" class="Your\Bundle\Manager\PaymentManager">
  <argument type="service" id="doctrine" />
</service>
```

####Step 4: Create your transaction controller
```php
namespace Your\Bundle\Controller

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class YourTransactionController extends Controller
{

    /**
     * @Route("/your/transaction/url")
     */
    public function yourTransactionAction()
    {
      //Validate, manage or do whatever you want to do with the transaction request
      ...

      //Just make sure you return this:
      $transactionHandler = $this->get('undf_zooz_payment.handler.open_transaction');
      $transactionHandler->setLanguage($this->getRequest()->getLocale());
      return $transactionHandler->openTransaction($payment);
    }
}
```


##Client side
####Step 1: Include the required scripts
Add following line in your payment template:
```
{% include 'UndfZoozPaymentBundle:Transaction:start.html.twig' %}
```

####Step 2: Enable the AngularJs controller
Add the following attribute to your "Pay" button HTML element:
```
  data-ng-controller="ZoozPaymentCtrl"
```

####Step 3: Trigger the Zooz payment window
On the callback of your transaction controller url call the "startZooz" function:
```javascript
$http.get('/your/transaction/url').success(function(response) {
  startZooz(response);
})
```

####Step 4: (only for AJAX mode) Handle the transaction response from the Zooz server
There can be multiple ways to handle the succeed of failed transaction responses, so what have been done
in this bundle is to emit two AngularJs events from the "ZoozPaymentCtrl" controller. So, in order to handle
the response from the Zooz server, you can listen for those events and do whatever you want afterwards.

This is an example made inside a controller which manages the scope where the "ZoozPaymentCtrl" controller is located:
```javacript
function MyCustomCtrl($scope, $window) {
  $scope.$on('zooz.payment.success', function(event, response) {
    $window.location.replace('/transaction/success');
  });

  $scope.$on('zooz.payment.error', function(event, response) {
    $window.location.replace('/transaction/error');
  });
}
```






