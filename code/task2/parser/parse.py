xml = open ('input.xml', 'rb').read ().decode ('utf-8');

m_el = '<entry>'
m_er = '</entry>'
m_il = '<id>'
m_ir = '</id>'
m_tl = '<title>'
m_tr = '</title>'
m_al = '<author>'
m_ar = '</author>'
m_anl = '<name>'
m_anr = '</name>'

def m_find (s, m_l, m_r, o = 0) :
	f = -1
	r = 0
	l = s.find (m_l, o)
	if l != -1 :
		l += len (m_l)
		r = s.find (m_r, l)
		f = s[l:r]
		r += len (m_r)
	return (f, r)
def escape (s) :
	return s.replace ('\'', '\'\'')
	
part = xml
sql = ''
m_sql = "INSERT INTO publications (title, author, link) VALUES ('%s', '%s', '%s');\r\n"
while True :
	(e, er) = m_find (part, m_el, m_er)
	if e != -1 :
		(i, _) = m_find (e, m_il, m_ir)
		i = escape (i)
		i = i.replace ('\n', '')
		(t, _) = m_find (e, m_tl, m_tr)
		t = t.replace ('\n', '')
		t = escape (t)
		print (t.encode('utf-8'))
		ax = []
		ao = 0
		while True:
			(a, ao) = m_find (e, m_al, m_ar, ao)
			if a != -1 :
				(an, _) = m_find (a, m_anl, m_anr)
				ax.append (an)
			else :
				break
		sql += m_sql % (t, ','.join (ax), i)
		part = part[er:]
	else :
		break

open ('parsed.sql', 'wb').write (sql.encode ('utf-8'));