<?php

namespace App\Classes;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\SchemaOrg\Schema;

class SchemaGenerator
{
    protected $settings;
    protected $themeSettings;

    public function __construct()
    {
        $this->settings = settings();
        $this->themeSettings = themeSettings();
    }

    public function render($__env, $method = null, $options = [])
    {
        $method = $method ? 'handle' . Str::studly($method) . 'Schema' : 'handleDefaultSchema';
        $schemas = $this->{$method}($__env, $options);
        $jsonSchemas = '';
        foreach ($schemas as $schema) {
            $jsonSchemas .= json_encode($schema, JSON_UNESCAPED_SLASHES);
        }
        return $jsonSchemas;
    }

    public function handleDefaultSchema($__env, $options = [])
    {
        $organizationSchema = Schema::organization()
            ->name($this->settings->general->site_name)
            ->url(url('/'))
            ->logo(asset($this->themeSettings->general->logo_dark));

        if ($this->settings->general->contact_email) {
            $organizationSchema->contactPoint([
                Schema::contactPoint()
                    ->email($this->settings->general->contact_email)
                    ->contactType('Contact'),
            ]);
        }

        $websiteSchema = Schema::webSite()
            ->url(url('/'))
            ->potentialAction(
                Schema::searchAction()
                    ->target(route('items.index', ['search' => '{search_term_string}']))
                    ->setProperty('query-input', 'required name=search_term_string')
            );

        $webPageSchema = Schema::webPage()
            ->name(pageTitle($__env));
        if ($__env->yieldContent('description')) {
            $webPageSchema->description($__env->yieldContent('description'));
        }
        $webPageSchema->publisher(
            Schema::organization()
                ->name($this->settings->general->site_name)
        );

        return [
            $organizationSchema->toArray(),
            $websiteSchema->toArray(),
            $webPageSchema->toArray(),
        ];
    }

    public function handleArticleSchema($__env, $options = [])
    {
        $article = $options['article'];

        $articleSchema = Schema::article()
            ->headline($article->title)
            ->author(Schema::organization()->name($this->settings->general->site_name))
            ->datePublished($article->created_at->format('Y-m-d'))
            ->dateModified($article->updated_at->format('Y-m-d'))
            ->mainEntityOfPage($article->getLink())
            ->image($article->getImageLink())
            ->publisher(
                Schema::organization()
                    ->name($this->settings->general->site_name)
                    ->logo(Schema::imageObject()->url(asset($this->themeSettings->general->logo_dark)))
            )
            ->description($article->short_description);

        return [
            $articleSchema->toArray(),
        ];
    }

    public function handleItemSchema($__env, $options = [])
    {
        $item = $options['item'];

        $itemSchema = Schema::product()
            ->category($item->category->name)
            ->url($item->getLink())
            ->description(shorterText(strip_tags($item->description), 160))
            ->name($item->name)
            ->image($item->getPreviewImageLink())
            ->brand(Schema::brand()->name($item->author->username))
            ->sku($item->id)
            ->mpn("E-{$item->id}")
            ->offers(
                Schema::offer()
                    ->price(number_format($item->price->regular, 2))
                    ->priceCurrency($this->settings->currency->code)
                    ->priceValidUntil(Carbon::now()->addDay())
                    ->itemCondition('http://schema.org/NewCondition')
                    ->availability('http://schema.org/InStock')
                    ->url($item->getLink()),
            );

        if ($item->hasReviews()) {
            $itemSchema->aggregateRating(
                Schema::aggregateRating()
                    ->ratingValue(number_format($item->avg_reviews, 2))
                    ->reviewCount($item->total_reviews)
            );

            $lastRating = $item->reviews->first();
            $itemSchema->review(
                Schema::review()
                    ->reviewRating(
                        Schema::rating()
                            ->ratingValue($lastRating->stars)
                            ->bestRating($lastRating->stars)
                    )->author(Schema::person()->name($lastRating->user->username))
            );
        }

        return [
            $itemSchema->toArray(),
        ];
    }

    public function handleItemBreadcrumbSchema($__env, $options = [])
    {
        $item = $options['item'];

        $breadcrumbs = [
            [
                'position' => 1,
                'name' => translate('Home'),
                'url' => route('home'),
            ],
            [
                'position' => 2,
                'name' => translate('Items'),
                'url' => route('items.index'),
            ],
            [
                'position' => 3,
                'name' => $item->category->name,
                'url' => $item->category->getLink(),
            ],
        ];

        $lastPosition = 4;
        if ($item->subCategory) {
            $breadcrumbs[] = [
                'position' => $lastPosition,
                'name' => $item->subCategory->name,
                'url' => $item->subCategory->getLink(),
            ];
            $lastPosition++;
        }

        $breadcrumbs[] = [
            'position' => $lastPosition,
            'name' => $item->name,
            'url' => $item->getLink(),
        ];

        $breadcrumbSchema = Schema::breadcrumbList();
        $listItems = [];

        foreach ($breadcrumbs as $breadcrumb) {
            $listItems[] = Schema::listItem()
                ->position($breadcrumb['position'])
                ->name($breadcrumb['name'])
                ->item($breadcrumb['url']);
        }

        $breadcrumbSchema->itemListElement($listItems);

        return [
            $breadcrumbSchema->toArray(),
        ];
    }

}