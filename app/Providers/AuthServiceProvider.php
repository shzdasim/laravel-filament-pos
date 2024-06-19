<?php
namespace App\Providers;

use App\Models\Application;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\SaleInvoice;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseInvoicePolicy;
use App\Policies\PurchaseReturnPolicy;
use App\Policies\SaleInvoicePolicy;
use App\Policies\SaleReturnPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Application::class => ApplicationPolicy::class,
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Customer::class => CustomerPolicy::class,
        SaleInvoice::class => SaleInvoicePolicy::class,
        PurchaseInvoice::class => PurchaseInvoicePolicy::class,
        SaleReturn::class => SaleReturnPolicy::class,
        PurchaseReturn::class => PurchaseReturnPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }

}
