SELECT DISTINCT
    p.public_id AS p_public_id,
    p.pdt_id AS p_pdt_id,
    p.sku AS p_sku,
    p.name AS p_name,
    p.slug AS p_slug,
    p.description AS p_description,
    p.short_description AS p_short_description,
    p.main_image AS p_main_image,
    p.main_video AS p_main_video,
    p.product_weight AS p_product_weight,
    p.product_dimension AS p_product_dimension,
    p.stock_quantity AS p_stock_quantity,
    p.allow_back_orders AS p_allow_back_orders,
    p.is_track_stock AS p_is_track_stock,
    p.is_featured AS p_is_featured,
    p.is_virtual AS p_is_virtual,
    p.is_downloadable AS p_is_downloadable,
    p.product_visibility AS p_product_visibility,
    p.total_sales AS p_total_sales,
    p.average_rating AS p_average_rating,
    p.review_count AS p_review_count,
    p.created_at AS p_created_at,
    p.updated_at AS p_updated_at,
    p.deleted_at AS p_deleted_at,
    p1.id AS p1_id,
    p1.status_code AS p1_status_code,
    p1.name AS p1_name,
    p1.description AS p1_description,
    p1.is_active AS p1_is_active,
    s.id AS s_id,
    s.stock_status_code AS s_stock_status_code,
    s.label AS s_label,
    s.description AS s_description,
    s.sort_order AS s_sort_order,
    c.cat_id AS c_cat_id,
    c.name AS c_name,
    b.br_id AS b_br_id,
    b.name AS b_name,
    p2.price_id AS p2_price_id,
    p2.region_code AS p2_region_code,
    p2.currency_id AS p2_currency_id,
    p2.base_price AS p2_base_price,
    p2.compare_price AS p2_compare_price,
    p2.cost_price AS p2_cost_price,
    p2.sale_price AS p2_sale_price,
    p2.price_includes_tax AS p2_price_includes_tax,
    p2.is_active AS p2_is_active,
    p3.id AS p3_id,
    p3.image_url AS p3_image_url,
    p3.alt_text AS p3_alt_text,
    p3.sort_order AS p3_sort_order,
    p4.id AS p4_id,
    p4.name AS p4_name,
    p4.variation_sku AS p4_variation_sku,
    p4.price_modifier AS p4_price_modifier,
    p4.stock_quantity AS p4_stock_quantity,
    p4.variation_status AS p4_variation_status,
    p4.created_at AS p4_created_at,
    p4.updated_at AS p4_updated_at,
    v.id AS v_id,
    v.name AS v_name,
    v1.id AS v1_id,
    v1.attribute_name AS v1_attribute_name,
    v1.attribute_value AS v1_attribute_value
FROM
    product AS p
    LEFT JOIN product_status AS p1 ON p.status_id = p1.id
    LEFT JOIN stock_status AS s ON p.stock_status_id = s.id
    LEFT JOIN category AS c ON p.category_id = c.cat_id
    LEFT JOIN brand AS b ON p.brand_id = b.br_id
    LEFT JOIN product_regional_price AS p2 ON p.pdt_id = p2.product_id
    LEFT JOIN product_image_gallery AS p3 ON p.pdt_id = p3.product_id
    LEFT JOIN product_variation AS p4 ON p.pdt_id = p4.product_id
    LEFT JOIN variation_type AS v ON p4.variation_type_id = v.id
    LEFT JOIN variation_attribute AS v1 ON p4.id = v1.variation_id
WHERE
    p.pdt_id = 1
ORDER BY
    p.pdt_id ASC,
    p3.sort_order ASC,
    v1.attribute_name ASC;