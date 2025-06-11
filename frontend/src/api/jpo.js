// src/api/jpo.js
const BASE_URL = "/api";

export async function fetchAdminJPOs() {
  try {
    console.log("🔍 Tentative de récupération des JPO admin...");
    
    const res = await fetch(`${BASE_URL}/admin/jpo`, {
      method: "GET",
      headers: { 
        "Content-Type": "application/json",
        "Authorization": `Bearer ${localStorage.getItem('token') || ''}`
      },
      credentials: "include"
    });
    
    console.log("📡 Réponse fetchAdminJPOs:", res.status, res.statusText);
    
    if (!res.ok) {
      const errorText = await res.text();
      console.error("❌ Erreur serveur fetchAdminJPOs:", errorText);
      throw new Error(`Erreur HTTP: ${res.status} - ${errorText}`);
    }
    
    const data = await res.json();
    console.log("✅ JPO admin récupérées:", data);
    return data;
    
  } catch (error) {
    console.error("💥 Erreur fetchAdminJPOs:", error);
    throw new Error("Erreur chargement des JPO");
  }
}

export async function createJPO(jpo) {
  try {
    console.log("🚀 Tentative de création JPO:", jpo);
    
    // ✅ Corrigé: utilise /jpo/create selon votre routeur
    const res = await fetch(`${BASE_URL}/jpo/create`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Authorization": `Bearer ${localStorage.getItem('token') || ''}`
      },
      credentials: "include",
      body: JSON.stringify(jpo),
    });
    
    console.log("📡 Réponse createJPO:", res.status, res.statusText);
    
    if (!res.ok) {
      const errorText = await res.text();
      console.error("❌ Erreur serveur createJPO:", errorText);
      throw new Error(`Erreur HTTP: ${res.status} - ${errorText}`);
    }
    
    const result = await res.json();
    console.log("✅ JPO créée avec succès:", result);
    return result;
    
  } catch (error) {
    console.error("💥 Erreur createJPO:", error);
    throw error;
  }
}

export async function updateJPO(id, jpo) {
  try {
    console.log(`🔄 Tentative de mise à jour JPO ${id}:`, jpo);
    
    const res = await fetch(`${BASE_URL}/jpo/update/${id}`, {
      method: "PUT",
      headers: { 
        "Content-Type": "application/json",
        "Authorization": `Bearer ${localStorage.getItem('token') || ''}`
      },
      credentials: "include",
      body: JSON.stringify(jpo),
    });
    
    console.log("📡 Réponse updateJPO:", res.status, res.statusText);
    
    if (!res.ok) {
      const errorText = await res.text();
      console.error("❌ Erreur serveur updateJPO:", errorText);
      throw new Error(`Erreur HTTP: ${res.status} - ${errorText}`);
    }
    
    const result = await res.json();
    console.log("✅ JPO mise à jour avec succès:", result);
    return result;
    
  } catch (error) {
    console.error("💥 Erreur updateJPO:", error);
    throw error;
  }
}

export async function deleteJPO(id) {
  try {
    console.log(`🗑️ Tentative de suppression JPO ${id}`);
    
    const res = await fetch(`${BASE_URL}/jpo/delete/${id}`, {
      method: "DELETE",
      headers: { 
        "Authorization": `Bearer ${localStorage.getItem('token') || ''}`
      },
      credentials: "include"
    });
    
    console.log("📡 Réponse deleteJPO:", res.status, res.statusText);
    
    if (!res.ok) {
      const errorText = await res.text();
      console.error("❌ Erreur serveur deleteJPO:", errorText);
      throw new Error(`Erreur HTTP: ${res.status} - ${errorText}`);
    }
    
    const result = await res.json();
    console.log("✅ JPO supprimée avec succès:", result);
    return result;
    
  } catch (error) {
    console.error("💥 Erreur deleteJPO:", error);
    throw error;
  }
}

export async function finishJPO(id) {
  try {
    console.log(`🏁 Tentative de finalisation JPO ${id}`);
    
    const res = await fetch(`${BASE_URL}/jpo/finish/${id}`, {
      method: "PUT",
      headers: { 
        "Authorization": `Bearer ${localStorage.getItem('token') || ''}`
      },
      credentials: "include"
    });
    
    console.log("📡 Réponse finishJPO:", res.status, res.statusText);
    
    if (!res.ok) {
      const errorText = await res.text();
      console.error("❌ Erreur serveur finishJPO:", errorText);
      throw new Error(`Erreur HTTP: ${res.status} - ${errorText}`);
    }
    
    const result = await res.json();
    console.log("✅ JPO finalisée avec succès:", result);
    return result;
    
  } catch (error) {
    console.error("💥 Erreur finishJPO:", error);
    throw error;
  }
}

export async function cancelJPO(id) {
  try {
    console.log(`❌ Tentative d'annulation JPO ${id}`);
    
    const res = await fetch(`${BASE_URL}/jpo/cancel/${id}`, {
      method: "PUT",
      headers: { 
        "Authorization": `Bearer ${localStorage.getItem('token') || ''}`
      },
      credentials: "include"
    });
    
    console.log("📡 Réponse cancelJPO:", res.status, res.statusText);
    
    if (!res.ok) {
      const errorText = await res.text();
      console.error("❌ Erreur serveur cancelJPO:", errorText);
      throw new Error(`Erreur HTTP: ${res.status} - ${errorText}`);
    }
    
    const result = await res.json();
    console.log("✅ JPO annulée avec succès:", result);
    return result;
    
  } catch (error) {
    console.error("💥 Erreur cancelJPO:", error);
    throw error;
  }
}

// 🆕 Fonction utilitaire pour tester la connexion API
export async function testAPI() {
  try {
    console.log("🧪 Test de connexion API...");
    
    const res = await fetch(`${BASE_URL}/test-api.php`);
    
    if (!res.ok) {
      throw new Error(`Test API échoué: ${res.status}`);
    }
    
    const result = await res.json();
    console.log("✅ API fonctionne:", result);
    return result;
    
  } catch (error) {
    console.error("💥 Test API échoué:", error);
    throw error;
  }
}