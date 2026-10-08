<?php
declare(strict_types=1);

/** Daily data begins when analytics-daily-migration.sql is installed. */
function dashboard_period_analytics(PDO $db, int $pageId, string $period): array {
    $today = new DateTimeImmutable((string)$db->query('SELECT CURRENT_DATE()')->fetchColumn());
    if ($period === 'weeks') {
        $first = $today->modify('monday this week')->modify('-7 weeks');
        $bucketCount = 8;
        $step = '+1 week';
        $label = static fn(DateTimeImmutable $date): string => $date->format('j M');
        $bucketKey = static fn(DateTimeImmutable $date): string => $date->modify('monday this week')->format('Y-m-d');
        $periodLabel = 'Last 8 weeks';
    } elseif ($period === 'months') {
        $first = $today->modify('first day of this month')->modify('-11 months');
        $bucketCount = 12;
        $step = '+1 month';
        $label = static fn(DateTimeImmutable $date): string => $date->format('M y');
        $bucketKey = static fn(DateTimeImmutable $date): string => $date->format('Y-m-01');
        $periodLabel = 'Last 12 months';
    } else {
        $first = $today->modify('-6 days');
        $bucketCount = 7;
        $step = '+1 day';
        $label = static fn(DateTimeImmutable $date): string => $date->format('D j');
        $bucketKey = static fn(DateTimeImmutable $date): string => $date->format('Y-m-d');
        $periodLabel = 'Last 7 days';
    }
    $end = $today->modify('+1 day');
    $previousEnd = $first;
    $previousFirst = $first->modify('-' . $first->diff($end)->days . ' days');
    $firstDate = $first->format('Y-m-d');
    $endDate = $end->format('Y-m-d');
    $previousFirstDate = $previousFirst->format('Y-m-d');
    $previousEndDate = $previousEnd->format('Y-m-d');

    $buckets = [];
    $cursor = $first;
    for ($i = 0; $i < $bucketCount; $i++) {
        $buckets[$cursor->format('Y-m-d')] = ['label'=>$label($cursor),'visits'=>0,'views'=>0,'clicks'=>0,'impressions'=>0];
        $cursor = $cursor->modify($step);
    }
    $q = $db->prepare('SELECT event_day,visits FROM store_analytics_daily WHERE page_id=? AND event_day>=? AND event_day<?');
    $q->execute([$pageId,$firstDate,$endDate]);
    foreach ($q as $row) {
        $key = $bucketKey(new DateTimeImmutable($row['event_day']));
        if (isset($buckets[$key])) $buckets[$key]['visits'] += (int)$row['visits'];
    }
    $q = $db->prepare('SELECT a.event_day,SUM(a.impressions) impressions,SUM(a.clicks) clicks,SUM(a.views) views FROM product_analytics_daily a JOIN page_posts p ON p.id=a.product_id WHERE p.page_id=? AND a.event_day>=? AND a.event_day<? GROUP BY a.event_day');
    $q->execute([$pageId,$firstDate,$endDate]);
    foreach ($q as $row) {
        $key = $bucketKey(new DateTimeImmutable($row['event_day']));
        if (!isset($buckets[$key])) continue;
        foreach (['impressions','clicks','views'] as $metric) $buckets[$key][$metric] += (int)$row[$metric];
    }
    $sum = static function(PDO $db, int $pageId, string $start, string $end): array {
        $q=$db->prepare('SELECT COALESCE(SUM(visits),0) FROM store_analytics_daily WHERE page_id=? AND event_day>=? AND event_day<?');
        $q->execute([$pageId,$start,$end]);
        $metrics=['visits'=>(int)$q->fetchColumn()];
        $q=$db->prepare('SELECT COUNT(DISTINCT visitor_id) FROM store_daily_visitors WHERE page_id=? AND event_day>=? AND event_day<?');
        $q->execute([$pageId,$start,$end]);
        $metrics['visitors']=(int)$q->fetchColumn();
        $q=$db->prepare('SELECT COALESCE(SUM(a.impressions),0) impressions,COALESCE(SUM(a.clicks),0) clicks,COALESCE(SUM(a.views),0) views FROM product_analytics_daily a JOIN page_posts p ON p.id=a.product_id WHERE p.page_id=? AND a.event_day>=? AND a.event_day<?');
        $q->execute([$pageId,$start,$end]);
        return $metrics + array_map('intval',$q->fetch(PDO::FETCH_ASSOC));
    };
    $current = $sum($db,$pageId,$firstDate,$endDate);
    $previous = $sum($db,$pageId,$previousFirstDate,$previousEndDate);
    $q=$db->prepare('SELECT p.id,p.title,p.slug,COALESCE(SUM(a.impressions),0) impressions,COALESCE(SUM(a.clicks),0) clicks,COALESCE(SUM(a.views),0) views FROM page_posts p LEFT JOIN product_analytics_daily a ON a.product_id=p.id AND a.event_day>=? AND a.event_day<? WHERE p.page_id=? GROUP BY p.id,p.title,p.slug ORDER BY views DESC,p.id DESC');
    $q->execute([$firstDate,$endDate,$pageId]);
    return ['current'=>$current,'previous'=>$previous,'buckets'=>array_values($buckets),'products'=>$q->fetchAll(PDO::FETCH_ASSOC),'label'=>$periodLabel,'from'=>$firstDate,'through'=>$today->format('Y-m-d')];
}

function dashboard_metric_change(int $current, int $previous): string {
    if ($previous === 0) return $current === 0 ? 'No change from previous period' : 'New activity this period';
    $percent = (int)round(($current-$previous)*100/$previous);
    return ($percent > 0 ? '+' : '') . $percent . '% vs previous period';
}
