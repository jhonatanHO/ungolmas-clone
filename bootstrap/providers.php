<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Ugm\Admin\Providers\AdminServiceProvider;
use Ugm\Ads\Providers\AdsServiceProvider;
use Ugm\Avatar\Providers\AvatarServiceProvider;
use Ugm\Config\Providers\ConfigServiceProvider;
use Ugm\Game\Providers\GameServiceProvider;
use Ugm\Gamification\Providers\GamificationServiceProvider;
use Ugm\News\Providers\NewsServiceProvider;
use Ugm\Ranking\Providers\RankingServiceProvider;
use Ugm\Store\Providers\StoreServiceProvider;
use Ugm\User\Providers\UserServiceProvider;
use Ugm\WebApp\Providers\WebAppServiceProvider;

return [
    AppServiceProvider::class,

    // UNGOLMAS Core Modules
    ConfigServiceProvider::class,
    UserServiceProvider::class,
    AdminServiceProvider::class,
    WebAppServiceProvider::class,
    GamificationServiceProvider::class,
    GameServiceProvider::class,

    // UNGOLMAS Integrations
    NewsServiceProvider::class,
    StoreServiceProvider::class,
    AvatarServiceProvider::class,
    RankingServiceProvider::class,
    AdsServiceProvider::class,
];
