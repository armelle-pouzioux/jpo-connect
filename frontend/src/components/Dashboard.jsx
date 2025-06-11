import { useEffect, useState } from "react";
import { fetchAdminJPOs, createJPO, updateJPO } from "../api/jpo";
import FormJPO from "./FormJPO";

export default function Dashboard() {
  const [jpos, setJpos] = useState([]);
  const [editingJPO, setEditingJPO] = useState(null);
  const [showForm, setShowForm] = useState(false);


  const refresh = async () => {
    try {
      const data = await fetchAdminJPOs();
      setJpos(data.jpos);
    } catch (err) {
      console.error("Erreur lors du chargement :", err.message);
    }
  };

  useEffect(() => {
    refresh();
  }, []);

  console.log("showForm =", showForm);


  const handleCreate = async (jpoData) => {
    await createJPO(jpoData);
    setShowForm(false);
    refresh();
  };

  const handleUpdate = async (jpoData) => {
    await updateJPO(editingJPO.id, jpoData);
    setEditingJPO(null);
    setShowForm(false);
    refresh();
  };

   return (
    <div>
      <h2>Dashboard Administrateur</h2>

      {showForm ? (
        <FormJPO
          initialData={editingJPO}
          onSubmit={editingJPO ? handleUpdate : handleCreate}
          onCancel={() => {
            setEditingJPO(null);
            setShowForm(false);
          }}
        />
      ) : (
        <button onClick={() => setShowForm(true)}>➕ Ajouter une JPO</button>
      )}

      <ul>
        {jpos.map((jpo) => (
          <li key={jpo.id}>
            📍 <strong>{jpo.place}</strong> — {jpo.date_jpo} ({jpo.status})
            <button onClick={() => {
              setEditingJPO(jpo);
              setShowForm(true);
            }}>✏️ Modifier</button>
          </li>
        ))}
      </ul>
    </div>
  );
}
