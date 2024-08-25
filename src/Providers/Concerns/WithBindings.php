<?php

declare(strict_types=1);

namespace Revolution\Ordering\Providers\Concerns;

use Revolution\Ordering\Actions\AddCartAction;
use Revolution\Ordering\Actions\AddHistoryAction;
use Revolution\Ordering\Actions\Api\MenusIndexAction;
use Revolution\Ordering\Actions\LoginAction;
use Revolution\Ordering\Actions\LogoutAction;
use Revolution\Ordering\Actions\OrderAction;
use Revolution\Ordering\Actions\ResetCartAction;
use Revolution\Ordering\Auth\OrderingRequestGuard;
use Revolution\Ordering\Cart\SessionCart;
use Revolution\Ordering\Contracts\Actions\AddCart;
use Revolution\Ordering\Contracts\Actions\AddHistory;
use Revolution\Ordering\Contracts\Actions\Api\MenusIndex;
use Revolution\Ordering\Contracts\Actions\Login;
use Revolution\Ordering\Contracts\Actions\Logout;
use Revolution\Ordering\Contracts\Actions\Order;
use Revolution\Ordering\Contracts\Actions\ResetCart;
use Revolution\Ordering\Contracts\Auth\OrderingGuard;
use Revolution\Ordering\Contracts\Cart\CartFactory;
use Revolution\Ordering\Contracts\Menu\MenuData;
use Revolution\Ordering\Contracts\Menu\MenuStorage;
use Revolution\Ordering\Contracts\Payment\PaymentFactory;
use Revolution\Ordering\Contracts\Payment\PaymentMethodFactory;
use Revolution\Ordering\Menu\MenuManager;
use Revolution\Ordering\Menu\SampleMenu;
use Revolution\Ordering\Payment\PaymentManager;
use Revolution\Ordering\Payment\PaymentMethod;

trait WithBindings
{
    protected function registerBindings(): void
    {
        $this->app->scoped(OrderingGuard::class, OrderingRequestGuard::class);
        $this->app->scoped(Login::class, LoginAction::class);
        $this->app->scoped(Logout::class, LogoutAction::class);
        $this->app->scoped(MenuStorage::class, MenuManager::class);
        $this->app->scoped(MenuData::class, SampleMenu::class);
        $this->app->scoped(AddCart::class, AddCartAction::class);
        $this->app->scoped(ResetCart::class, ResetCartAction::class);
        $this->app->scoped(Order::class, OrderAction::class);
        $this->app->scoped(AddHistory::class, AddHistoryAction::class);
        $this->app->scoped(PaymentFactory::class, PaymentManager::class);
        $this->app->scoped(PaymentMethodFactory::class, PaymentMethod::class);
        $this->app->scoped(CartFactory::class, SessionCart::class);

        $this->app->scoped(MenusIndex::class, MenusIndexAction::class);
    }
}
