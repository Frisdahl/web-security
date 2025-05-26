document
  .getElementById("commentForm")
  ?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target as HTMLFormElement);

    await fetch("http://localhost:8080/post_comment.php", {
      method: "POST",
      body: formData,
      credentials: "include",
    });
  });
