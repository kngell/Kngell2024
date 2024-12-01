<?php

declare(strict_types=1);

class UserOrdersHTMLElement extends AbstractHTMLElement
{
    private CollectionInterface $orderList;

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        $this->orderList = $params['orderList'];
    }

    public function display(): array
    {
        $html = '';
        if ($this->orderList->count() > 0) {
            $orderList = $this->orderList->offsetGet('orders');
            foreach ($orderList as $order) {
                $temp = str_replace('{{ord_date}}', $order->created_at, $this->getTemplate('showOrdersPath'));
                $temp = str_replace('{{ord_ttc}}', $order->ord_amount_ttc, $temp);
                $temp = str_replace('{{ord_userFullName}}', $order->firstName . '&nbsp;' . $order->lastName, $temp);
                $temp = str_replace('{{ord_number}}', $order->ord_number, $temp);
                $temp = str_replace('{{ord_deliveryDate}}', $order->ord_delivery_date, $temp);
                $temp = str_replace('{{ord_status}}', (string) $order->status, $temp);
                $temp = str_replace('{{ord_itemInfos}}', $this->orderDetailsInfos($order), $temp);
                $html .= $temp;
            }
        }
        return ['userOrders' => $html];
    }

    protected function orderDetailsInfos(object $order) : string
    {
        $html = '';
        $template = $this->getTemplate('itemInfosPath');
        $orderDetails = $this->orderList->offsetGet('order_details')->filter(function ($odr) use ($order) {
            return $odr->od_order_id === $order->ord_id;
        });
        if ($orderDetails->count() > 0) {
            foreach ($orderDetails as $od) {
                $temp = str_replace('{{ord_itemDescr}}', $od->short_descr, $template);
                $temp = str_replace('{{ord_itemtitle}}', $od->title, $temp);
                $temp = str_replace('{{ord_itemImg}}', $this->media($od), $temp);
                $html .= $temp;
            }
        }

        return $html;
    }
}