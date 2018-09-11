<?php

namespace PlentyConnector\tests\Unit\Adapter\ShopwareAdapter\ResponseParser;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\IdentityService\IdentityService;
use PlentyConnector\Connector\IdentityService\Model\Identity;
use Ramsey\Uuid\Uuid;

abstract class ResponseParserTest extends TestCase
{
    /**
     * @var array
     */
    protected static $orderData;

    /**
     * @var IdentityService
     */
    protected $identityService;

    /**
     * @var string
     */
    protected $objectIdentifier;

    public static function setupBeforeClass()
    {
        self::$orderData = unserialize('a:42:{s:2:"id";i:15;s:6:"number";s:5:"20001";s:10:"customerId";i:2;s:9:"paymentId";i:4;s:10:"dispatchId";i:9;s:9:"partnerId";s:0:"";s:6:"shopId";i:1;s:13:"invoiceAmount";d:998.55999999999995;s:16:"invoiceAmountNet";d:839.13;s:15:"invoiceShipping";d:0;s:18:"invoiceShippingNet";d:0;s:9:"orderTime";O:8:"DateTime":3:{s:4:"date";s:26:"2012-08-30 10:15:54.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Berlin";}s:13:"transactionId";s:13:"transactionId";s:7:"comment";s:0:"";s:15:"customerComment";s:0:"";s:15:"internalComment";s:0:"";s:3:"net";i:1;s:7:"taxFree";i:0;s:11:"temporaryId";s:0:"";s:7:"referer";s:0:"";s:11:"clearedDate";N;s:12:"trackingCode";s:0:"";s:11:"languageIso";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";d:1;s:13:"remoteAddress";s:14:"217.86.205.141";s:10:"deviceType";N;s:7:"details";a:3:{i:0;a:21:{s:2:"id";i:42;s:7:"orderId";i:15;s:9:"articleId";i:197;s:5:"taxId";i:1;s:7:"taxRate";d:19;s:8:"statusId";i:0;s:6:"number";s:5:"20001";s:13:"articleNumber";s:7:"SW10196";s:5:"price";d:836.13400000000001;s:8:"quantity";i:1;s:11:"articleName";s:20:"ESD Download Artikel";s:7:"shipped";i:0;s:12:"shippedGroup";i:0;s:11:"releaseDate";O:8:"DateTime":3:{s:4:"date";s:27:"-0001-11-30 00:00:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Berlin";}s:4:"mode";i:0;s:10:"esdArticle";i:1;s:6:"config";s:0:"";s:3:"ean";N;s:4:"unit";N;s:8:"packUnit";N;s:9:"attribute";a:8:{s:2:"id";i:1;s:13:"orderDetailId";i:42;s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}i:1;a:21:{s:2:"id";i:43;s:7:"orderId";i:15;s:9:"articleId";i:0;s:5:"taxId";i:0;s:7:"taxRate";d:19;s:8:"statusId";i:0;s:6:"number";s:5:"20001";s:13:"articleNumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";d:-2;s:8:"quantity";i:1;s:11:"articleName";s:15:"Warenkorbrabatt";s:7:"shipped";i:0;s:12:"shippedGroup";i:0;s:11:"releaseDate";O:8:"DateTime":3:{s:4:"date";s:27:"-0001-11-30 00:00:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Berlin";}s:4:"mode";i:4;s:10:"esdArticle";i:0;s:6:"config";s:0:"";s:3:"ean";N;s:4:"unit";N;s:8:"packUnit";N;s:9:"attribute";a:8:{s:2:"id";i:2;s:13:"orderDetailId";i:43;s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}i:2;a:21:{s:2:"id";i:44;s:7:"orderId";i:15;s:9:"articleId";i:0;s:5:"taxId";i:0;s:7:"taxRate";d:19;s:8:"statusId";i:0;s:6:"number";s:5:"20001";s:13:"articleNumber";s:19:"sw-payment-absolute";s:5:"price";d:5;s:8:"quantity";i:1;s:11:"articleName";s:25:"Zuschlag für Zahlungsart";s:7:"shipped";i:0;s:12:"shippedGroup";i:0;s:11:"releaseDate";O:8:"DateTime":3:{s:4:"date";s:27:"-0001-11-30 00:00:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Berlin";}s:4:"mode";i:4;s:10:"esdArticle";i:0;s:6:"config";s:0:"";s:3:"ean";N;s:4:"unit";N;s:8:"packUnit";N;s:9:"attribute";a:8:{s:2:"id";i:3;s:13:"orderDetailId";i:44;s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:9:"documents";a:0:{}s:7:"payment";a:21:{s:2:"id";i:4;s:4:"name";s:7:"invoice";s:11:"description";s:8:"Rechnung";s:8:"template";s:11:"invoice.tpl";s:5:"class";s:11:"invoice.php";s:5:"table";s:0:"";s:4:"hide";b:0;s:21:"additionalDescription";s:210:"Sie zahlen einfach und bequem auf Rechnung. Shopware bietet z.B. auch die Möglichkeit, Rechnung automatisiert erst ab der 2. Bestellung für Kunden zur Verfügung zu stellen, um Zahlungsausfälle zu vermeiden.";s:12:"debitPercent";d:0;s:9:"surcharge";d:5;s:15:"surchargeString";s:0:"";s:8:"position";i:3;s:6:"active";b:1;s:9:"esdActive";b:1;s:14:"mobileInactive";b:0;s:11:"embedIFrame";s:0:"";s:12:"hideProspect";i:0;s:6:"action";s:0:"";s:8:"pluginId";N;s:6:"source";N;s:9:"attribute";N;}s:13:"paymentStatus";a:6:{s:2:"id";i:12;s:4:"name";s:15:"completely_paid";s:11:"description";s:16:"Komplett bezahlt";s:8:"position";i:4;s:5:"group";s:7:"payment";s:8:"sendMail";i:0;}s:11:"orderStatus";a:6:{s:2:"id";i:0;s:4:"name";s:4:"open";s:11:"description";s:5:"Offen";s:8:"position";i:1;s:5:"group";s:5:"state";s:8:"sendMail";i:1;}s:8:"customer";a:29:{s:2:"id";i:2;s:9:"paymentId";i:4;s:8:"groupKey";s:1:"H";s:6:"shopId";i:1;s:12:"priceGroupId";N;s:11:"encoderName";s:3:"md5";s:12:"hashPassword";s:32:"352db51c3ff06159d380d3d9935ec814";s:6:"active";b:1;s:5:"email";s:17:"mustermann@b2b.de";s:10:"firstLogin";O:8:"DateTime":3:{s:4:"date";s:26:"2012-08-30 00:00:00.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Berlin";}s:9:"lastLogin";O:8:"DateTime":3:{s:4:"date";s:26:"2012-08-30 11:43:17.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:13:"Europe/Berlin";}s:11:"accountMode";i:0;s:15:"confirmationKey";s:0:"";s:9:"sessionId";s:40:"66e9b10064a19b1fcf6eb9310c0753866c764836";s:10:"newsletter";i:0;s:10:"validation";s:1:"0";s:9:"affiliate";i:0;s:13:"paymentPreset";i:4;s:10:"languageId";s:1:"1";s:7:"referer";s:0:"";s:15:"internalComment";s:0:"";s:12:"failedLogins";i:0;s:11:"lockedUntil";N;s:10:"salutation";s:2:"mr";s:5:"title";N;s:9:"firstname";s:8:"Händler";s:6:"number";s:5:"20003";s:8:"lastname";s:18:"Kundengruppe-Netto";s:8:"birthday";N;}s:16:"paymentInstances";a:0:{}s:7:"billing";a:22:{s:2:"id";i:1;s:7:"orderId";i:15;s:10:"customerId";i:2;s:9:"countryId";i:2;s:7:"stateId";i:3;s:7:"company";s:3:"B2B";s:10:"department";s:7:"Einkauf";s:10:"salutation";s:2:"mr";s:5:"title";N;s:6:"number";N;s:9:"firstName";s:8:"Händler";s:8:"lastName";s:18:"Kundengruppe-Netto";s:6:"street";s:11:"Musterweg 1";s:7:"zipCode";s:5:"00000";s:4:"city";s:11:"Musterstadt";s:5:"phone";s:13:"012345 / 6789";s:5:"vatId";s:0:"";s:22:"additionalAddressLine1";N;s:22:"additionalAddressLine2";N;s:7:"country";a:15:{s:2:"id";i:2;s:4:"name";s:11:"Deutschland";s:3:"iso";s:2:"DE";s:7:"isoName";s:7:"GERMANY";s:8:"position";i:1;s:11:"description";s:0:"";s:12:"shippingFree";b:0;s:7:"taxFree";i:0;s:12:"taxFreeUstId";i:0;s:19:"taxFreeUstIdChecked";i:0;s:6:"active";b:1;s:4:"iso3";s:3:"DEU";s:26:"displayStateInRegistration";b:0;s:24:"forceStateInRegistration";b:0;s:6:"areaId";i:1;}s:5:"state";a:6:{s:2:"id";i:3;s:9:"countryId";i:2;s:8:"position";i:0;s:4:"name";s:19:"Nordrhein-Westfalen";s:9:"shortCode";s:2:"NW";s:6:"active";i:1;}s:9:"attribute";a:8:{s:2:"id";i:1;s:14:"orderBillingId";i:1;s:5:"text1";N;s:5:"text2";N;s:5:"text3";N;s:5:"text4";N;s:5:"text5";N;s:5:"text6";N;}}s:8:"shipping";a:19:{s:2:"id";i:1;s:7:"orderId";i:15;s:9:"countryId";i:2;s:7:"stateId";i:3;s:10:"customerId";i:2;s:7:"company";s:3:"B2B";s:10:"department";s:7:"Einkauf";s:10:"salutation";s:2:"mr";s:9:"firstName";s:8:"Händler";s:5:"title";N;s:8:"lastName";s:18:"Kundengruppe-Netto";s:6:"street";s:11:"Musterweg 1";s:7:"zipCode";s:5:"00000";s:4:"city";s:11:"Musterstadt";s:22:"additionalAddressLine1";N;s:22:"additionalAddressLine2";N;s:9:"attribute";a:8:{s:2:"id";i:1;s:15:"orderShippingId";i:1;s:5:"text1";N;s:5:"text2";N;s:5:"text3";N;s:5:"text4";N;s:5:"text5";N;s:5:"text6";N;}s:7:"country";a:15:{s:2:"id";i:2;s:4:"name";s:11:"Deutschland";s:3:"iso";s:2:"DE";s:7:"isoName";s:7:"GERMANY";s:8:"position";i:1;s:11:"description";s:0:"";s:12:"shippingFree";b:0;s:7:"taxFree";i:0;s:12:"taxFreeUstId";i:0;s:19:"taxFreeUstIdChecked";i:0;s:6:"active";b:1;s:4:"iso3";s:3:"DEU";s:26:"displayStateInRegistration";b:0;s:24:"forceStateInRegistration";b:0;s:6:"areaId";i:1;}s:5:"state";a:6:{s:2:"id";i:3;s:9:"countryId";i:2;s:8:"position";i:0;s:4:"name";s:19:"Nordrhein-Westfalen";s:9:"shortCode";s:2:"NW";s:6:"active";i:1;}}s:4:"shop";a:18:{s:2:"id";i:1;s:6:"mainId";N;s:10:"categoryId";i:3;s:4:"name";s:7:"Deutsch";s:5:"title";N;s:8:"position";i:0;s:4:"host";s:13:"192.168.33.10";s:8:"basePath";s:9:"/shopware";s:7:"baseUrl";N;s:5:"hosts";s:0:"";s:6:"secure";b:0;s:12:"alwaysSecure";b:0;s:10:"secureHost";N;s:14:"secureBasePath";N;s:10:"templateId";i:22;s:7:"default";b:1;s:6:"active";b:1;s:13:"customerScope";b:0;}s:8:"dispatch";a:28:{s:2:"id";i:9;s:4:"name";s:16:"Standard Versand";s:4:"type";i:0;s:11:"description";s:0:"";s:7:"comment";s:0:"";s:6:"active";b:1;s:8:"position";i:1;s:11:"calculation";i:1;s:20:"surchargeCalculation";i:3;s:14:"taxCalculation";i:0;s:12:"shippingFree";N;s:11:"multiShopId";N;s:15:"customerGroupId";N;s:16:"bindShippingFree";i:0;s:12:"bindTimeFrom";N;s:10:"bindTimeTo";N;s:11:"bindInStock";N;s:13:"bindLastStock";i:0;s:15:"bindWeekdayFrom";N;s:13:"bindWeekdayTo";N;s:14:"bindWeightFrom";N;s:12:"bindWeightTo";s:5:"1.000";s:13:"bindPriceFrom";N;s:11:"bindPriceTo";N;s:7:"bindSql";N;s:10:"statusLink";s:0:"";s:14:"calculationSql";N;s:9:"attribute";N;}s:9:"attribute";a:8:{s:2:"id";i:1;s:7:"orderId";i:15;s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}s:15:"languageSubShop";a:19:{s:2:"id";i:1;s:6:"mainId";N;s:10:"categoryId";i:3;s:4:"name";s:7:"Deutsch";s:5:"title";N;s:8:"position";i:0;s:4:"host";s:13:"192.168.33.10";s:8:"basePath";s:9:"/shopware";s:7:"baseUrl";N;s:5:"hosts";s:0:"";s:6:"secure";b:0;s:12:"alwaysSecure";b:0;s:10:"secureHost";N;s:14:"secureBasePath";N;s:10:"templateId";i:22;s:7:"default";b:1;s:6:"active";b:1;s:13:"customerScope";b:0;s:6:"locale";a:4:{s:2:"id";i:1;s:6:"locale";s:5:"de_DE";s:8:"language";s:7:"Deutsch";s:9:"territory";s:11:"Deutschland";}}s:15:"paymentStatusId";i:12;s:13:"orderStatusId";i:0;}');
    }

    protected function setUp()
    {
        $this->objectIdentifier = Uuid::uuid4()->toString();

        $identity = $this->createMock(Identity::class);
        $identity->expects($this->any())->method('getObjectIdentifier')->willReturn($this->objectIdentifier);

        /**
         * @var IdentityService|\PHPUnit_Framework_MockObject_MockObject $identityService
         */
        $identityService = $this->createMock(IdentityService::class);
        $identityService->expects($this->any())->method('findOneBy')->willReturn($identity);
        $identityService->expects($this->any())->method('findOneOrThrow')->willReturn($identity);
        $identityService->expects($this->any())->method('findOneOrCreate')->willReturn($identity);
        $identityService->expects($this->any())->method('isMappedIdentity')->willReturn(true);

        $this->identityService = $identityService;
    }
}
