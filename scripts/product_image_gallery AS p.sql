UPDATE product_image_gallery AS p
INNER JOIN (
    SELECT
        'monet&eacute;l&eacute; gallery image 3' AS alt_text,
        3 AS sort_order,
        17 AS id
    UNION ALL
    SELECT
        'monet&eacute;l&eacute; gallery image 4',
        4,
        18
) AS s2 ON p.id = s2.id
SET
    p.`alt_text` = COALESCE(s2.`alt_text`, p.`alt_text`),
    p.`sort_order` = COALESCE(s2.`sort_order`, p.`sort_order`);