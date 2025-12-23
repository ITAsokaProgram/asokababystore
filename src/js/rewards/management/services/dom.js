// services/dom.js
// Cache selector untuk efisiensi
const cache = new Map();
export function $(id) {
  if (!cache.has(id)) cache.set(id, document.getElementById(id));
  return cache.get(id);
}
