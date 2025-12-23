cd /var/www/asokababystore.com

# ==========================================
# 1. CUSTOM FILTER (Data Kemarin)
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=custom&start_date=$(date -d 'yesterday' +%Y-%m-%d)&end_date=$(date -d 'yesterday' +%Y-%m-%d)&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=custom&start_date=$(date -d 'yesterday' +%Y-%m-%d)&end_date=$(date -d 'yesterday' +%Y-%m-%d)&page=1&limit=10&search=&sort_by=belanja&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_products.php "filter_type=custom&start_date=$(date -d 'yesterday' +%Y-%m-%d)&end_date=$(date -d 'yesterday' +%Y-%m-%d)&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_products.php "filter_type=custom&start_date=$(date -d 'yesterday' +%Y-%m-%d)&end_date=$(date -d 'yesterday' +%Y-%m-%d)&page=1&limit=10&search=&sort_by=belanja&status=active&type=all"

# ==========================================
# 2. PRESET: KEMARIN
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_filter.php "filter_type=preset&filter=kemarin&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=preset&filter=kemarin&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=preset&filter=kemarin&status=active&page=1&limit=10&search=&sort_by=belanja"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_location.php "filter_type=preset&filter=kemarin&status=active&level=city&limit=default"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_member_product_pairs.php "filter_type=preset&filter=kemarin&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_age.php "filter_type=preset&filter=kemarin&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_frequency.php "filter_type=preset&filter=kemarin&status=active&limit=10&page=1"

# ==========================================
# 3. PRESET: 1 MINGGU
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_filter.php "filter_type=preset&filter=1minggu&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=preset&filter=1minggu&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=preset&filter=1minggu&status=active&page=1&limit=10&search=&sort_by=belanja"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_location.php "filter_type=preset&filter=1minggu&status=active&level=city&limit=default"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_member_product_pairs.php "filter_type=preset&filter=1minggu&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_age.php "filter_type=preset&filter=1minggu&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_frequency.php "filter_type=preset&filter=1minggu&status=active&limit=10&page=1"

# ==========================================
# 4. PRESET: 1 BULAN
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_filter.php "filter_type=preset&filter=1bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=preset&filter=1bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=preset&filter=1bulan&status=active&page=1&limit=10&search=&sort_by=belanja"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_location.php "filter_type=preset&filter=1bulan&status=active&level=city&limit=default"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_member_product_pairs.php "filter_type=preset&filter=1bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_age.php "filter_type=preset&filter=1bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_frequency.php "filter_type=preset&filter=1bulan&status=active&limit=10&page=1"

# ==========================================
# 5. PRESET: 3 BULAN
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_filter.php "filter_type=preset&filter=3bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=preset&filter=3bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=preset&filter=3bulan&status=active&page=1&limit=10&search=&sort_by=belanja"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_location.php "filter_type=preset&filter=3bulan&status=active&level=city&limit=default"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_member_product_pairs.php "filter_type=preset&filter=3bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_age.php "filter_type=preset&filter=3bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_frequency.php "filter_type=preset&filter=3bulan&status=active&limit=10&page=1"

# ==========================================
# 6. PRESET: 6 BULAN
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_filter.php "filter_type=preset&filter=6bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=preset&filter=6bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=preset&filter=6bulan&status=active&page=1&limit=10&search=&sort_by=belanja"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_location.php "filter_type=preset&filter=6bulan&status=active&level=city&limit=default"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_member_product_pairs.php "filter_type=preset&filter=6bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_age.php "filter_type=preset&filter=6bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_frequency.php "filter_type=preset&filter=6bulan&status=active&limit=10&page=1"

# ==========================================
# 7. PRESET: 9 BULAN
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_filter.php "filter_type=preset&filter=9bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=preset&filter=9bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=preset&filter=9bulan&status=active&page=1&limit=10&search=&sort_by=belanja"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_location.php "filter_type=preset&filter=9bulan&status=active&level=city&limit=default"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_member_product_pairs.php "filter_type=preset&filter=9bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_age.php "filter_type=preset&filter=9bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_frequency.php "filter_type=preset&filter=9bulan&status=active&limit=10&page=1"

# ==========================================
# 8. PRESET: 12 BULAN
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_filter.php "filter_type=preset&filter=12bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_member.php "filter_type=preset&filter=12bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_members.php "filter_type=preset&filter=12bulan&status=active&page=1&limit=10&search=&sort_by=belanja"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_location.php "filter_type=preset&filter=12bulan&status=active&level=city&limit=default"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_member_product_pairs.php "filter_type=preset&filter=12bulan&status=active&limit=10"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_member_by_age.php "filter_type=preset&filter=12bulan&status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/management/get_top_members_by_frequency.php "filter_type=preset&filter=12bulan&status=active&limit=10&page=1"

# ==========================================
# 9. PRODUK UMUM
# ==========================================
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_top_products.php "status=active"
/usr/bin/php /var/www/asokababystore.com/src/api/member/product/get_paginated_products.php "page=1&limit=10&search=&sort_by=belanja&status=active&type=all"