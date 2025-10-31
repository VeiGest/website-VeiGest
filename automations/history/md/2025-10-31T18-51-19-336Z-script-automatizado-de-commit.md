# script automatizado de commit
**Commit:** `58457a4c83626e518e18da3e0b51e15e467b17bb`
**Data:** 2025-10-31T18:51:19.336Z
**Tags:** feature
## Descrição
Desenvolvimento de um script para automatizar e padronizar os commits do projeto.
## Diff (do commit)
```diff
commit 58457a4c83626e518e18da3e0b51e15e467b17bb
Author:     LyeZinho <pedrokalebdej1@gmail.com>
AuthorDate: Fri Oct 31 18:51:19 2025 +0000
Commit:     LyeZinho <pedrokalebdej1@gmail.com>
CommitDate: Fri Oct 31 18:51:19 2025 +0000

    script automatizado de commit
    
    Desenvolvimento de um script para automatizar e padronizar os commits do projeto.

diff --git a/automations/commit.md b/automations/commit.md
new file mode 100644
index 0000000..822495b
--- /dev/null
+++ b/automations/commit.md
@@ -0,0 +1,7 @@
+---
+title: script automatizado de commit
+description: Desenvolvimento de um script para automatizar e padronizar os commits do projeto.
+tags: feature
+---
+  
+    
\ No newline at end of file
diff --git a/automations/commiter.js b/automations/commiter.js
new file mode 100644
index 0000000..31b62ee
--- /dev/null
+++ b/automations/commiter.js
@@ -0,0 +1,144 @@
+const fs = require('fs');
+const path = require('path');
+const { spawnSync } = require('child_process');
+
+const repoRoot = path.resolve(__dirname, '..');
+const automationsDir = __dirname;
+const commitFile = path.join(automationsDir, 'commit.md');
+const historyMdDir = path.join(automationsDir, 'history', 'md');
+const historyPdfDir = path.join(automationsDir, 'history', 'pdf');
+
+function readCommitMeta(filePath) {
+    if (!fs.existsSync(filePath)) throw new Error('commit.md não encontrado: ' + filePath);
+    const content = fs.readFileSync(filePath, 'utf8');
+    const trimmed = content.trim();
+    if (!trimmed.startsWith('---')) return { title: '', description: '', raw: content };
+
+    const parts = trimmed.split('---');
+    // parts: ['', '\nkey: value\n...', '\nrest?'] => frontmatter in parts[1]
+    const front = parts[1] || '';
+    const rest = parts.slice(2).join('---').trim();
+    const meta = {};
+    front.split(/\r?\n/).forEach(line => {
+        const idx = line.indexOf(':');
+        if (idx > -1) {
+            const key = line.slice(0, idx).trim();
+            const val = line.slice(idx + 1).trim();
+            meta[key] = val.replace(/^<|>$/g, '').trim();
+        }
+    });
+    return {
+        title: meta.title || '',
+        description: meta.description || '',
+        tags: (meta.tags || '').split(',').map(t => t.trim()).filter(Boolean),
+        raw: rest
+    };
+}
+
+function slugify(s) {
+    return s.toString().toLowerCase()
+        .normalize('NFKD').replace(/[\u0300-\u036F]/g, '')
+        .replace(/[^a-z0-9]+/g, '-')
+        .replace(/^-+|-+$/g, '')
+        .slice(0, 60) || Date.now().toString();
+}
+
+function ensureDir(dir) {
+    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
+}
+
+function runGit(args, options = {}) {
+    const res = spawnSync('git', args, { cwd: repoRoot, encoding: 'utf8', ...options });
+    if (res.error) throw res.error;
+    return res;
+}
+
+function main() {
+    try {
+        const meta = readCommitMeta(commitFile);
+        const subject = meta.title || 'Atualização';
+        const body = meta.description || meta.raw || '';
+
+        // Stage changes
+        const add = runGit(['add', '-A'], { stdio: 'inherit' });
+        if (add.status !== 0) throw new Error('git add falhou');
+
+        // Commit
+        const commitArgs = ['commit', '-m', subject];
+        if (body) commitArgs.push('-m', body);
+        const commitRes = runGit(commitArgs);
+        if (commitRes.status !== 0) {
+            // If no changes to commit, abort gracefully
+            const stdout = (commitRes.stdout || '') + (commitRes.stderr || '');
+            if (/nothing to commit|no changes added/.test(stdout.toLowerCase())) {
+                console.log('Nada para commitar.');
+                return;
+            }
+            throw new Error('git commit falhou: ' + stdout);
+        }
+
+        // Get last commit diff and metadata
+        const revRes = runGit(['rev-parse', 'HEAD']);
+        const commitHash = (revRes.stdout || '').trim();
+
+        const showRes = runGit(['show', 'HEAD', '--patch', '--pretty=fuller']);
+        const show = showRes.stdout || showRes.stderr || '';
+
+        // Compose markdown
+        const date = new Date().toISOString();
+        const slug = slugify(subject);
+        ensureDir(historyMdDir);
+        ensureDir(historyPdfDir);
+        const mdFilename = `${date.replace(/[:.]/g, '-')}-${slug}.md`;
+        const pdfFilename = `${date.replace(/[:.]/g, '-')}-${slug}.pdf`;
+        const mdPath = path.join(historyMdDir, mdFilename);
+        const pdfPath = path.join(historyPdfDir, pdfFilename);
+
+        const tagsLine = (meta.tags && meta.tags.length) ? meta.tags.join(', ') : '';
+
+        const mdContent = [
+            `# ${subject}`,
+            '',
+            `**Commit:** \`${commitHash}\``,
+            '',
+            `**Data:** ${date}`,
+            '',
+            tagsLine ? `**Tags:** ${tagsLine}` : '',
+            '',
+            '## Descrição',
+            '',
+            body || '_(sem descrição)_',
+            '',
+            '## Diff (do commit)',
+            '',
+            '```diff',
+            show,
+            '```'
+        ].filter(Boolean).join('\n');
+
+        fs.writeFileSync(mdPath, mdContent, 'utf8');
+        console.log('Arquivo MD criado:', mdPath);
+
+        // Converter para PDF usando pandoc (se disponível)
+        const pandocCheck = runGit(['--version']); // quick check: reuse git to avoid extra spawn error path
+        // Try pandoc
+        const pandoc = spawnSync('pandoc', ['--version'], { encoding: 'utf8' });
+        if (pandoc.status === 0) {
+            const conv = spawnSync('pandoc', [mdPath, '-o', pdfPath], { encoding: 'utf8' });
+            if (conv.status === 0) {
+                console.log('PDF criado:', pdfPath);
+            } else {
+                console.warn('Falha ao gerar PDF com pandoc. Saída:', conv.stderr || conv.stdout);
+            }
+        } else {
+            console.warn('pandoc não encontrado. Pulei geração de PDF.');
+        }
+
+        console.log('Commit automatizado concluído: ', commitHash);
+    } catch (err) {
+        console.error('Erro:', err.message || err);
+        process.exit(1);
+    }
+}
+
+if (require.main === module) main();
\ No newline at end of file

```