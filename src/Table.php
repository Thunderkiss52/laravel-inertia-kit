<?php
namespace Thunderkiss52\LaravelInertiaKit;


use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use App\Filters\RequestFilter;
use Illuminate\Support\Collection;
use Spatie\Navigation\Navigation;
use Spatie\QueryBuilder\QueryBuilder;
use App\Services\LaravelDocuments\DocumentFactory;

class Table
{
    public static function create(
        string $label,
        string $item,
        \Closure $format,
        \Closure $filter = null,
        string $link = null,
        array $fields = null,
        string $filters = null,
        ?Navigation $breadcrumbs = null,
        ?Navigation $actions = null,
        array $docs = null,
        array $sorts = [],
        array $initdata = [],
        int $modalwidth = 540,
        bool $lazy = false,
        bool $short = false,
        bool $inertia = true,
        array $columnWidth = [],
        bool $tabs = false,
        string $form = null,
        string $component = '@thunderkiss52/UI/TableData',
        array $other = []
    ) {

        if (request()->has('export')) {
            $items = self::getItems($item, $format, $sorts, $filters ? $filters::$filters : [], $filter);
            if (request()->has('headers') && !is_null(request()->query('headers'))) {
                $keys = explode(',', request()->query('headers'));
            } else {
                $keys = array_keys($items->first());
            }
            $convertedKeys = [];
            foreach ($keys as $k) {
                if ($k == 'link') {
                    $convertedKeys[] = __('Link');
                } elseif ($k == 'avatar') {
                    $convertedKeys[] = __('Avatar');
                } else {
                    $convertedKeys[] = $k;
                }
            }

            return response()->download(DocumentFactory::table(
                $items
                    ->transform(fn($item) => collect($item)->only($keys)->all())
                    ->map(function ($i) use ($keys) {
                        foreach ($keys as $key) {
                            if ($key == 'link') {
                                $i['link'] = [
                                    'name' => __('Link'),
                                    'link' => $i[$key]
                                ];
                            } else if ($key == 'avatar') {
                                $i['avatar'] = [
                                    'name' => __('Avatar'),
                                    'link' => $i[$key]
                                ];
                            } else if (is_object($i[$key])) {
                                $i[$key] = [
                                    'name' => $i[$key]->title,
                                    'link' => $i[$key]->url
                                ];
                            }
                        }
                        return $i;
                    })->all(),
                $convertedKeys,
                true,
                [],
                array_map(fn($value) => $value / 8, $columnWidth)
            ), "{$label}.xlsx");
        }
        $lazyItems = request()->header('x-inertia-partial-data');
        $lazyItems = $lazyItems ? explode(',', $lazyItems) : [];
        $items_data = match ($lazy) {
            true => in_array('items', $lazyItems) ? self::getItems($item, $format, $sorts, $filters ? $filters::$filters : [], $filter) : null,
            false => self::getItems($item, $format, $sorts, $filters ? $filters::$filters : [], $filter)
        };
        $data = [
            'label' => $label,
            'modalwidth' => $modalwidth,
            'item' => $item,
            'short' => $short,
            'docs' => collect($docs)->map(fn($doc) => [
                'label' => $doc::label(),
                'link' => '/document/',
            ]),
            'columnWidth' => $columnWidth,
            'filters' => $filters ? $filters::filters() : null,
            'actions' => $actions ? $actions->breadcrumbs() : [],
            'lazy' => $lazy,
            'headers' => match ($lazy) {
                true => in_array('items', $lazyItems) ? Inertia::lazy(fn() => $items_data->count() > 0 ? array_keys($items_data->first()) : []) : null,
                false => $items_data->count() > 0 ? array_keys($items_data->first()) : []
            },
            'items' => match ($lazy) {
                true => $items_data ? self::processItems($items_data, $sorts) : null,
                false => self::processItems($items_data, $sorts)
            },
            'breadcrumbs' => $breadcrumbs ? $breadcrumbs->breadcrumbs() : null,
            'link' => $link,
            'fields' => $fields,
            'initdata' => $initdata,
            'tabs' => $tabs,
            'exportTemplateLink' => $form ? '/forms/table?form=' . $form : null,
            'draftsLink' => $form ? route('forms.draft.store', [
                'form' => $form
            ]) : null,
            "locale" => [
                "addStrokeDown" => __("AddStrokeDown"), //"Добавить строку снизу",
                "addStrokeUp" => __("AddStrokeUp"), //"Добавить строку сверху",
                "gotoPage" => __("GotoPage"), //"Перейти на страницу",
                "selected" => __("Selected"), //"Выбрано",
                "retry" => __("Retry"), //"Повторить",
                "logs" => __("Logs"), //"Логи",
                "docs" => __("Docs"), //"Документы",
                "attention" => __("Attention"), //"Внимание",
                "data" => __("Data"), //"Данные",
                "filters" => __("Filters"), //"Фильтры",
                "areYouWant" => __("AreYouWant"), //"Фильтры",
                "apply" => __("Apply"), //"Фильтры",
                "table" => __("Table"), //"Фильтры",
                "row" => __("Row"), //"Фильтры",
                "stroke" => __("Stroke"), //"Фильтры",
                "multipleUpload" => __("MultipleUpload"), //"Фильтры",
                "dragOrSelect" => __("DragOrSelect"), //"Переместите или выберите файл"
                "emptyLogs" => __("emptyLogs"),
                //Generic 
                "add" => __("Add"), //Добавить
                "delete" => __("Delete"), //Удалить
                "update" => __("Update"),
                "action" => __("Action"),
                "user" => __("User"),
                "actions" => __("Actions"),
                "search" => __("Search"), //Поиск
                "record" => __("Record"), //Запись
                "edit" => __("Edit"), //Редактировать
                "close" => __("Close"),
                "clear" => __("Clear"), //"Очистить",
                "download" => __("Export"), //"Скачать",
                "load" => __("Load"), //"Загрузить",
                "create" => __("Create"), //Создать
                "error" => __("Internal Server Error"), //, Не удалось ${page.props.locale.delete}
                "deleted" => __("Gone"), //: "Данные удалены",
                "select" => __("Select"), // "Выбрать"
                "reset" => __("Reset Content"), // "Очистить" Сбросить
            ],
            ...$other
        ];
        return $inertia ? Inertia::render($component, $data) : $data;
    }

    private static function getItems(string $class, \Closure $format, array $sorts, array $filters, \Closure $filter = null)
    {

        $items = $class::query();
        if (request()->query('search')) {
            $items = $class::search(request()->query('search'));
        }

        $usr = auth()->user();
        if (method_exists($usr, 'relationModel') && $usr->relationModel()) {
            $items = $items->whereIn('id', $usr->relationModel()->getAccessIds($class));
        }

        if ($items instanceof \Laravel\Scout\Builder) {
            $sortKey = request()->query('sort', '');
            $isDesc = mb_substr(request()->query('sort', ' '), 0, 1) == '-';
            $sortKey = str_replace('-', '', $sortKey);
            $needSort = in_array($sortKey, $sorts);
            if ($needSort && mb_strlen($sortKey) > 0) {
                $items = $isDesc ? $items->orderByDesc($sortKey) : $items->orderBy($sortKey);
            }
        } else {
            $items = QueryBuilder::for($items)
                ->allowedSorts($sorts)
                ->allowedFilters($filters);
        }

        $posts = ($filter ? $filter($items) : $items)->paginate();
        $formattedData = $posts->getCollection()->map($format);

        // Создаем новый экземпляр пагинатора с отформатированными данными
        return new LengthAwarePaginator(
            $formattedData,
            $posts->total(),
            $posts->perPage(),
            $posts->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // throw new Exception(json_encode($res));
        // $res->items = $res->items->map($format); //array_map($format, );
        // return $res;
    }
    private static function processItems(LengthAwarePaginator $items, $sorts)
    {
        $items = $items->toArray();
        $sortKey = request()->query('sort', '');
        $isDesc = mb_substr($sortKey, 0, 1) === '-';
        $sortKey = ltrim($sortKey, '-');
        $needSort = !in_array($sortKey, $sorts) && $sortKey !== '';

        $items['data'] = collect($items['data'])
            ->when($needSort, function($collection) use ($sortKey, $isDesc) {
                return $collection->sortBy([
                    $sortKey => $isDesc ? 'desc' : 'asc'
                ], SORT_REGULAR)->values();
            })
            ->map(fn($item) => array_values($item))
            ->all();

        return $items;
    }

    public static function card(
        $item,
        array $fields,
        ?\Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection $files = null,
        string $avatar = null,
        ?array $tabs = null,
        ?Navigation $breadcrumbs = null,
        array $statistic = [],
        array $data = [],
        array $links = [],
        ?Navigation $actions = null,
    ) {

        if (request()->has('export')) {
            $items = collect($tabs[request()->query('export')]['items']());
            $keys = array_keys($items->first());
            $convertedKeys = [];
            foreach ($keys as $k) {
                if ($k == 'link') {
                    $convertedKeys[] = __('Link');
                } elseif ($k == 'avatar') {
                    $convertedKeys[] = __('Avatar');
                } else {
                    $convertedKeys[] = $k;
                }
            }
            return response()->download(DocumentFactory::table(
                $items->map(function ($i) use ($keys) {
                    foreach ($keys as $key) {
                        if ($key == 'link') {
                            $i['link'] = [
                                'name' => __('Link'),
                                'link' => $i[$key]
                            ];
                        }

                        if ($key == 'avatar') {
                            $i['avatar'] = [
                                'name' => __('Avatar'),
                                'link' => $i[$key]
                            ];
                        }

                        if (is_object($i[$key])) {
                            $i[$key] = [
                                'name' => $i[$key]->title,
                                'link' => $i[$key]->url
                            ];
                        }
                    }
                    return $i;
                })->all(),
                $convertedKeys,
                true,
                [],
                array_map(fn($value) => $value / 8, Arr::get($tabs[request()->query('export')], 'columnWidth', [])),
            ), $item['name'] . " - " . request()->query('export') . ".xlsx");
        } elseif (request()->has('tab')) {
            return $tabs[request()->query('tab')]['items']();
        }

        $t = [];
        foreach ($tabs as $tname => $tdata) {
            $ttemp = $tdata;
            unset($ttemp['items']);
            $t[$tname] = $ttemp;
        }

        return Inertia::render('Template/TabsCard', [
            'links' => $links,
            'actions' => $actions ? $actions->tree() : null,
            'item' => $item,
            'avatar' => $avatar,
            'files' => $files,
            'data' => $data,
            'tabs' => $t, // $tabs,
            'statistic' => $statistic,
            'breadcrumbs' => $breadcrumbs ? $breadcrumbs->tree() : null,
            'fields' => $fields
        ]);
    }
}
