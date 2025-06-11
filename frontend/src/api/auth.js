// src/api/auth.js
const BASE_URL = "/api";

export async function login(email, password) {
  try {
    const res = await fetch(`${BASE_URL}/auth/login`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json"
      },
      credentials: "include",
      body: JSON.stringify({ email, password }),
    });
    
    const data = await res.json();
    
    if (data.success && data.token) {
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
    }
    
    return data;
  } catch (error) {
    console.error("Erreur login:", error);
    throw error;
  }
}

export async function register(userData) {
  try {
    console.log("🚀 Envoi des données:", userData);
    
    const res = await fetch(`${BASE_URL}/auth/register`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json"
      },
      credentials: "include",
      body: JSON.stringify(userData),
    });
    
    console.log("📡 Status:", res.status);
    console.log("📡 Headers:", res.headers);
    
    // ✅ Lire la réponse comme texte d'abord
    const responseText = await res.text();
    console.log("📄 Réponse brute:", responseText);
    
    // Vérifier si c'est du JSON valide
    if (responseText.startsWith('<')) {
      console.error("❌ Le serveur a renvoyé du HTML:", responseText.substring(0, 200));
      throw new Error("Le serveur a renvoyé du HTML au lieu de JSON");
    }
    
    // Essayer de parser le JSON
    const data = JSON.parse(responseText);
    console.log("✅ JSON parsé:", data);
    
    return data;
    
  } catch (error) {
    console.error("💥 Erreur register:", error);
    throw error;
  }
}

export async function logout() {
  try {
    const res = await fetch(`${BASE_URL}/auth/logout`, {
      method: "POST",
      credentials: "include"
    });
    
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    
    return await res.json();
  } catch (error) {
    console.error("Erreur logout:", error);
    throw error;
  }
}

export async function getCurrentUser() {
  try {
    const res = await fetch(`${BASE_URL}/auth/me`, {
      method: "GET",
      credentials: "include"
    });
    
    return await res.json();
  } catch (error) {
    console.error("Erreur getCurrentUser:", error);
    throw error;
  }
}

export function getStoredUser() {
  const user = localStorage.getItem('user');
  return user ? JSON.parse(user) : null;
}

export function isLoggedIn() {
  return !!localStorage.getItem('token');
}