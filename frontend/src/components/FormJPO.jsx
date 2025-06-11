import { useState, useEffect } from "react";

export default function FormJPO({ initialData = null, onSubmit, onCancel }) {
  const [form, setForm] = useState({
    description: "",
    date: "",
    time: "",
    place: "Marseille",
    capacity: 30,
    status: "upcoming"
  });

  useEffect(() => {
    if (initialData) {
      const [datePart, timePart] = initialData.date_jpo.split(" ");
      setForm({
        description: initialData.description,
        date: datePart,
        time: timePart,
        place: initialData.place,
        capacity: initialData.capacity,
        status: initialData.status
      });
    }
  }, [initialData]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(form);
  };

  return (
    <form onSubmit={handleSubmit}>
      <h3>{initialData ? "Modifier une JPO" : "Créer une JPO"}</h3>
      <input name="description" value={form.description} onChange={handleChange} placeholder="Description" required />
      <input name="date" type="date" value={form.date} onChange={handleChange} required />
      <input name="time" type="time" value={form.time} onChange={handleChange} required />
      <select name="place" value={form.place} onChange={handleChange}>
        {["Marseille", "Paris", "Cannes", "Martigues", "Toulon", "Brignoles"].map(city => (
          <option key={city} value={city}>{city}</option>
        ))}
      </select>
      <input name="capacity" type="number" min="1" value={form.capacity} onChange={handleChange} required />
      <select name="status" value={form.status} onChange={handleChange}>
        {["upcoming", "finished", "canceled"].map(status => (
          <option key={status} value={status}>{status}</option>
        ))}
      </select>
      <button type="submit">Valider</button>
      {onCancel && <button type="button" onClick={onCancel}>Annuler</button>}
    </form>
  );
}
