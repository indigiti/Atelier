'use client';
import { useEffect, useState } from 'react';
const card = 'rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5';
export default function Operations() {
  const [state, setState] = useState({ loading: true });
  const [refresh, setRefresh] = useState(0);
  useEffect(() => {
    const controller = new AbortController();
    setState({ loading: true });
    fetch(new URL('../admin/dashboard.json', window.location.href), { credentials: 'same-origin', cache: 'no-store', signal: controller.signal })
      .then(async response => {
        if (response.status === 401) throw new Error('Sign in to Atelier Admin to view operations.');
        if (!response.ok) throw new Error('Operations data could not be loaded. Use the PHP dashboard or try again.');
        return response.json();
      })
      .then(data => setState({ data }))
      .catch(error => { if (error.name !== 'AbortError') setState({ error: error.message }); });
    return () => controller.abort();
  }, [refresh]);
  return <main className="mx-auto max-w-6xl p-3 sm:p-6">
    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
      <div><p className="text-xs font-semibold uppercase tracking-widest text-stone-600">Atelier · Staging</p><h1 className="text-2xl font-semibold">Operations overview</h1></div>
      <button className="rounded-lg border border-stone-300 bg-white px-4 py-2 text-sm font-medium disabled:opacity-50" disabled={state.loading} onClick={() => setRefresh(n => n + 1)}>Refresh</button>
    </div>
    {state.loading && <p role="status" className={card}>Loading operations…</p>}
    {state.error && <p role="alert" className={card}>{state.error}</p>}
    {state.data && <>
      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        {state.data.queues.map(item => <section key={item.label} className={card}>
          <h2 className="text-sm font-medium text-stone-600">{item.label}</h2>
          <p className="mt-2 text-3xl font-semibold tabular-nums">{item.count}</p>
          {item.href && <a className="mt-3 inline-block text-sm font-semibold underline" href={item.href} target="_parent">Open queue</a>}
        </section>)}
      </div>
      <section className={card + ' mt-4'}>
        <h2 className="font-semibold">Release checks</h2>
        <p className="mt-1 text-sm text-stone-700">{state.data.release.passed} of {state.data.release.total} checks passed. Staging only.</p>
        <a className="mt-2 inline-block text-sm font-semibold underline" href={state.data.release.href} target="_parent">Review checks</a>
      </section>
    </>}
  </main>;
}
