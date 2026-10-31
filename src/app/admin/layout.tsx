import AdminNavbar from './AdminNavbar';

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-gray-100 flex flex-col font-sans">
      <AdminNavbar />
      <main className="flex-1 p-4 md:p-8 flex justify-center">
        <div className="w-full max-w-6xl">
          {children}
        </div>
      </main>
    </div>
  );
}
