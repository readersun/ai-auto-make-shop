<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">AI 상품 기획 관리</h1>
          <p class="mt-2 text-gray-600">AI가 기획한 상품을 검토하고 승인/거부할 수 있습니다.</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                상품명 검색
              </label>
              <input
                id="search"
                v-model="filterForm.search"
                type="text"
                placeholder="상품명을 입력하세요"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @input="debounceFilter"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                상태
              </label>
              <select
                id="status"
                v-model="filterForm.status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @change="applyFilters"
              >
                <option value="all">전체</option>
                <option value="pending">검토 대기</option>
                <option value="approved">승인됨</option>
                <option value="rejected">거부됨</option>
              </select>
            </div>

            <!-- Reset Button -->
            <div class="flex items-end">
              <button
                @click="resetFilters"
                class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
              >
                필터 초기화
              </button>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-sm font-medium text-gray-600">전체 상품</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ products.total }}</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-sm font-medium text-gray-600">검토 대기</div>
            <div class="mt-2 text-3xl font-bold text-yellow-600">
              {{ products.data.filter(p => p.status === 'pending').length }}
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-sm font-medium text-gray-600">승인됨</div>
            <div class="mt-2 text-3xl font-bold text-green-600">
              {{ products.data.filter(p => p.status === 'approved').length }}
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-sm font-medium text-gray-600">거부됨</div>
            <div class="mt-2 text-3xl font-bold text-red-600">
              {{ products.data.filter(p => p.status === 'rejected').length }}
            </div>
          </div>
        </div>

        <!-- Product List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  상품명
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  카테고리
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  가격
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  수익률
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  상태
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  생성일
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  작업
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="products.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  기획된 상품이 없습니다.
                </td>
              </tr>
              <tr
                v-for="product in products.data"
                :key="product.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                  <div class="text-sm text-gray-500">{{ product.target_customer }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ product.category }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatPrice(product.suggested_retail_price) }}
                  </div>
                  <div v-if="product.cost" class="text-xs text-gray-500">
                    원가: {{ formatPrice(product.cost.total_cost) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-semibold text-green-600">
                    {{ product.profit_margin }}%
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getStatusClass(product.status)"
                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  >
                    {{ getStatusText(product.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(product.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="`/admin/products/${product.id}`"
                    class="text-blue-600 hover:text-blue-900 transition"
                  >
                    상세보기
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="products.last_page > 1" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                전체 <span class="font-medium">{{ products.total }}</span>개 중
                <span class="font-medium">{{ products.from }}</span>-<span class="font-medium">{{ products.to }}</span>
              </div>
              <div class="flex space-x-2">
                <Link
                  v-for="link in products.links"
                  :key="link.label"
                  :href="link.url"
                  :class="[
                    'px-3 py-1 text-sm rounded',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : link.url
                      ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                      : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                  ]"
                  :disabled="!link.url"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  products: Object,
  filters: Object,
});

const filterForm = reactive({
  search: props.filters.search || '',
  status: props.filters.status || 'all',
});

let debounceTimeout = null;

const debounceFilter = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get('/admin/products', filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filterForm.search = '';
  filterForm.status = 'all';
  applyFilters();
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('ko-KR', {
    style: 'currency',
    currency: 'KRW',
  }).format(price);
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('ko-KR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(date);
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
  const texts = {
    pending: '검토 대기',
    approved: '승인됨',
    rejected: '거부됨',
  };
  return texts[status] || status;
};
</script>
