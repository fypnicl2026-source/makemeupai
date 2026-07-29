import api from "./api";

export async function getBeauticians(filters = {}) {
  const params = {};
  if (filters.city) params.city = filters.city;
  if (filters.specialization) params.specialization = filters.specialization;
  if (filters.gender_focus) params.gender_focus = filters.gender_focus;

  const { data } = await api.get("/api/beauticians", { params });
  return data.data.beauticians;
}

export async function getBeautician(id) {
  const { data } = await api.get(`/api/beauticians/${id}`);
  return data.data.beautician;
}
