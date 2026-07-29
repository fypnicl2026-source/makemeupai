import api, { getCsrf } from "./api";

export async function getFaceProfile() {
  const { data } = await api.get("/api/ai/face-profile");
  return data.data;
}

export async function uploadFaceAnalysis(formData) {
  await getCsrf();
  const { data } = await api.post("/api/ai/face-analysis", formData, {
    headers: { "Content-Type": "multipart/form-data" },
    transformRequest: [(payload) => payload],
  });
  return data.data;
}

export async function getLookRecommendations({ eventType, styleMood, gender }) {
  await getCsrf();
  const { data } = await api.post("/api/ai/look-recommendations", {
    eventType,
    styleMood,
    gender,
  });
  return data.data;
}
