import './styles.css';
export const metadata = { title: 'Atelier Operations', robots: { index: false, follow: false } };
export default function Layout({ children }) {
  return <html lang="en"><body>{children}</body></html>;
}
