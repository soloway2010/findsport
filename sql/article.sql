SELECT a.article, a.dealer
FROM shop a
INNER JOIN (
    SELECT article, MAX(price) price
    FROM shop
    GROUP BY article
) b ON a.article = b.article AND a.price = b.price
ORDER BY a.article

