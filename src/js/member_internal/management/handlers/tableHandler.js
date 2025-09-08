import { api } from "../services/api.js";
import { el } from "../services/dom.js";

export const tableHandler = async (limit, page) => {
  try {
    const res = await api.getNewMembers(limit, page);
    if (!res || !res.data) {
      el.TABLE_BODY.innerHTML = '<tr><td colspan="8" class="text-center py-6 text-gray-400">Tidak ada data</td></tr>';
      return { pagination: null };
    }
    const table = el.TABLE_BODY;
    table.innerHTML = "";
    let rows = "";
    res.data.forEach((member, index) => {
      const number = (page - 1) * limit + index + 1;
      rows += `
<tr class="hover:bg-blue-50 transition-colors duration-200">
    <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">${number}</td>
    <td class="px-6 py-4 whitespace-nowrap text-gray-900">${member.nama_lengkap ?? "-"}</td>
    <td class="px-6 py-4 whitespace-nowrap text-gray-600">${member.phone ?? "-"}</td>
    <td class="px-6 py-4 whitespace-nowrap text-gray-600">${member.email ?? "-"}</td>
    <td class="px-6 py-4 whitespace-nowrap text-gray-700">${member.cabang ?? "-"}</td>
    <td class="px-6 py-4 whitespace-nowrap text-gray-600">${member.tgl_daftar?.split(" ")[0] ?? "-"}</td>
    <td class="px-6 py-4 text-center">
        <span class="px-3 py-1 rounded-full text-xs font-semibold truncate ${
          member.status === "new member"
            ? "bg-green-100 text-green-700"
            : "bg-gray-100 text-gray-600"
        }">
            ${member.status}
        </span>
    </td>
    
</tr>`;
    });
    table.innerHTML = rows;
    el.MEMBER_BARU.textContent = res.pagination.total;
    return { pagination: res.pagination };
  } catch (error) {
    el.TABLE_BODY.innerHTML = '<tr><td colspan="8" class="text-center py-6 text-red-400">Gagal memuat data</td></tr>';
    console.error("Error loading table data:", error);
    return { pagination: null };
  }
};

