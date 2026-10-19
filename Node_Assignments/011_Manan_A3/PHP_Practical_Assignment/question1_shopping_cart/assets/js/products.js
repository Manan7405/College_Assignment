/**
 * Loads products with AJAX (fetch) and builds the pagination buttons.
 *
 * Flow:
 *   1. page opens -> loadProducts(1) for the selected category
 *   2. user clicks a category link  -> currentCategory changes -> loadProducts(1)
 *   3. user clicks a page button    -> loadProducts(thatPage)
 *   4. user clicks "Add to cart"    -> POST to ajax/cart_add.php
 *
 * The server (ajax/get_products.php) does the real pagination with SQL LIMIT/OFFSET.
 */
(function () {
    'use strict';

    var area     = document.getElementById('product-area');
    var grid     = document.getElementById('product-grid');
    var message  = document.getElementById('product-message');
    var pager    = document.getElementById('pagination');
    var title    = document.getElementById('products-title');
    var cartBadge = document.getElementById('cart-count');

    var currentCategory = parseInt(area.getAttribute('data-category'), 10) || 0;
    var csrfToken       = area.getAttribute('data-csrf');

    /* ---------- show / hide an info message ---------- */
    function showMessage(text, type) {
        message.className = 'alert alert-' + (type || 'info');
        message.textContent = text;
    }
    function hideMessage() {
        message.className = 'alert d-none';
        message.textContent = '';
    }

    /* ---------- load one page of products ---------- */
    function loadProducts(page) {
        var url = 'ajax/get_products.php?category_id=' + encodeURIComponent(currentCategory) +
                  '&page=' + encodeURIComponent(page);

        grid.innerHTML = '';
        pager.innerHTML = '';
        showMessage('Loading products...', 'secondary');

        fetch(url)
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Server error');
                    }
                    return data;
                });
            })
            .then(function (data) {
                title.textContent = data.category_name;
                renderProducts(data.products);
                renderPagination(data.page, data.total_pages);
            })
            .catch(function (error) {
                showMessage('Could not load products: ' + error.message, 'danger');
            });
    }

    /* ---------- build the product cards ---------- */
    function renderProducts(products) {
        grid.innerHTML = '';

        if (products.length === 0) {
            showMessage('No products are available in this category yet.', 'info');
            return;
        }
        hideMessage();

        products.forEach(function (p) {
            var col  = document.createElement('div');
            col.className = 'col';

            var card = document.createElement('div');
            card.className = 'card h-100 shadow-sm product-card';

            var img = document.createElement('img');
            img.src = p.image_url;
            img.alt = p.name;
            img.onerror = function () { this.onerror = null; this.src = 'assets/img/no-image.png'; };

            var body = document.createElement('div');
            body.className = 'card-body d-flex flex-column';

            var name = document.createElement('h5');
            name.className = 'card-title';
            name.textContent = p.name;                // textContent -> safe against XSS

            var price = document.createElement('p');
            price.className = 'product-price mb-3';
            price.textContent = p.price_formatted;

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-primary mt-auto';
            button.textContent = 'Add to cart';
            button.addEventListener('click', function () { addToCart(p.id, button); });

            body.appendChild(name);
            body.appendChild(price);
            body.appendChild(button);
            card.appendChild(img);
            card.appendChild(body);
            col.appendChild(card);
            grid.appendChild(col);
        });
    }

    /* ---------- build Previous / 1 2 3 / Next ---------- */
    function renderPagination(page, totalPages) {
        pager.innerHTML = '';
        if (totalPages <= 1) {
            return;                                    // one page only -> no buttons
        }

        function addItem(label, targetPage, disabled, active) {
            var li = document.createElement('li');
            li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            var a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = label;
            a.addEventListener('click', function (event) {
                event.preventDefault();
                if (!disabled && !active) {
                    loadProducts(targetPage);
                }
            });
            li.appendChild(a);
            pager.appendChild(li);
        }

        addItem('Previous', page - 1, page <= 1, false);
        for (var i = 1; i <= totalPages; i++) {
            addItem(String(i), i, false, i === page);
        }
        addItem('Next', page + 1, page >= totalPages, false);
    }

    /* ---------- add to cart (AJAX POST) ---------- */
    function addToCart(productId, button) {
        var formData = new FormData();
        formData.append('product_id', productId);
        formData.append('csrf_token', csrfToken);

        button.disabled = true;
        fetch('ajax/cart_add.php', { method: 'POST', body: formData })
            .then(function (response) {
                return response.json().then(function (data) { return { status: response.status, data: data }; });
            })
            .then(function (result) {
                if (result.data.success) {
                    if (cartBadge) { cartBadge.textContent = result.data.cart_count; }
                    button.textContent = 'Added!';
                    setTimeout(function () { button.textContent = 'Add to cart'; button.disabled = false; }, 900);
                } else {
                    button.disabled = false;
                    if (result.data.login_required) {
                        window.location.href = 'login.php';
                    } else {
                        alert(result.data.message || 'Could not add to cart.');
                    }
                }
            })
            .catch(function () {
                button.disabled = false;
                alert('Network error. Please try again.');
            });
    }

    /* ---------- category links ---------- */
    var links = document.querySelectorAll('.category-link');
    links.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();                    // stop the normal page reload
            currentCategory = parseInt(link.getAttribute('data-category'), 10) || 0;

            links.forEach(function (l) { l.classList.remove('active'); });
            link.classList.add('active');

            loadProducts(1);                           // always start on page 1 of the new category
        });
    });

    // first load
    loadProducts(1);
})();
