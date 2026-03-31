<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Carbon\Carbon $date
 * @property string $campaign_name
 * @property string $campaign_id
 * @property string $adset_name
 * @property string $adset_id
 * @property string $ad_name
 * @property string $ad_id
 * @property int $impressions
 * @property int $reach
 * @property int $clicks
 * @property float $spend
 * @property float $ctr
 * @property float $cpc
 * @property float $cpm
 * @property float $frequency
 * @property int $results
 * @property float $cost_per_result
 * @property int $link_click
 * @property int $link_click_unique
 * @property int $video_view
 * @property int $page_engagement
 * @property int $post_engagement
 * @property int $video_p25
 * @property int $video_p50
 * @property int $video_p75
 * @property int $video_p100
 * @property int $clicks_all
 * @property float $ctr_all
 * @property float $cpc_all
 * @property float $budget
 * @property string $status
 * @property string $stop_time
 */
class MetaAdsReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'campaign_name',
        'campaign_id',
        'adset_name',
        'adset_id',
        'ad_name',
        'ad_id',
        'impressions',
        'reach',
        'clicks',
        'spend',
        'ctr',
        'cpc',
        'cpm',
        'frequency',
        'results',
        'cost_per_result',
        'link_click',
        'link_click_unique',
        'video_view',
        'page_engagement',
        'post_engagement',
        'video_p25',
        'video_p50',
        'video_p75',
        'video_p100',
        'clicks_all',
        'ctr_all',
        'cpc_all',
        'budget',
        'status',
        'stop_time',
    ];

    protected $casts = [
        'date' => 'date',
        'impressions' => 'integer',
        'reach' => 'integer',
        'clicks' => 'integer',
        'spend' => 'decimal:2',
        'ctr' => 'decimal:4',
        'cpc' => 'decimal:4',
        'cpm' => 'decimal:4',
        'frequency' => 'decimal:4',
        'results' => 'integer',
        'cost_per_result' => 'decimal:4',
        'link_click' => 'integer',
        'link_click_unique' => 'integer',
        'video_view' => 'integer',
        'page_engagement' => 'integer',
        'post_engagement' => 'integer',
        'video_p25' => 'integer',
        'video_p50' => 'integer',
        'video_p75' => 'integer',
        'video_p100' => 'integer',
        'clicks_all' => 'integer',
        'ctr_all' => 'decimal:4',
        'cpc_all' => 'decimal:4',
        'budget' => 'decimal:2',
        'status' => 'string',
        'stop_time' => 'string',
    ];
}
