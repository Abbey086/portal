class SubjectSelection {
  constructor() {
    this.selectedSubjects = new Set()
    this.userType = null
    this.userId = null
    this.schoolId = null
    this.subjects = []

    this.init()
  }

  async init() {
    await this.loadUserInfo()
    await this.loadSubjects()
    await this.loadUserSubjects()
    this.setupEventListeners()
    this.updatePageTitle()
  }

  async loadUserInfo() {
    try {
      const response = await fetch("get_user_info.php")
      const data = await response.json()

      if (data.success) {
        this.userType = data.user_type
        this.userId = data.user_id
        this.schoolId = data.school_id

        document.getElementById("user-name").textContent = data.full_name
      } else {
        throw new Error(data.message)
      }
    } catch (error) {
      console.error("Error loading user info:", error)
      this.showNotification("Error loading user information", "error")
    }
  }

  async loadSubjects() {
    this.showLoading(true)

    try {
      const response = await fetch(`get_subjects.php?school_id=${this.schoolId}`)
      const data = await response.json()

      if (data.success) {
        this.subjects = data.subjects
        this.renderSubjects()
      } else {
        throw new Error(data.message)
      }
    } catch (error) {
      console.error("Error loading subjects:", error)
      this.showNotification("Error loading subjects", "error")
    } finally {
      this.showLoading(false)
    }
  }

  async loadUserSubjects() {
    try {
      const response = await fetch(`get_user_subjects.php?user_type=${this.userType}&user_id=${this.userId}`)
      const data = await response.json()

      if (data.success) {
        data.subjects.forEach((subject) => {
          this.selectedSubjects.add(subject.id)
        })
        this.updateSelectedDisplay()
        this.updateSubjectCards()
      }
    } catch (error) {
      console.error("Error loading user subjects:", error)
    }
  }

  renderSubjects() {
    const grid = document.getElementById("subjects-grid")
    grid.innerHTML = ""

    this.subjects.forEach((subject) => {
      const card = this.createSubjectCard(subject)
      grid.appendChild(card)
    })
  }

  createSubjectCard(subject) {
    const card = document.createElement("div")
    card.className = "subject-card"
    card.dataset.subjectId = subject.id
    card.style.setProperty("--subject-color", subject.color_theme)

    // Calculate some mock statistics
    const enrollmentCount = Math.floor(Math.random() * 150) + 50
    const progressPercentage = Math.floor(Math.random() * 100)

    card.innerHTML = `
            <div class="subject-header">
                <div class="subject-icon">
                    <i class="${subject.icon_class}"></i>
                </div>
                <div class="subject-stats">
                    <div>${enrollmentCount} ${this.userType === "teacher" ? "students" : "enrolled"}</div>
                    <div>${subject.subject_code}</div>
                </div>
            </div>
            <div class="subject-info">
                <h3>${subject.subject_name}</h3>
                <p>${subject.description}</p>
            </div>
            <div class="subject-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${progressPercentage}%"></div>
                </div>
                <div class="progress-text">${progressPercentage}% ${this.userType === "teacher" ? "taught" : "completed"}</div>
            </div>
        `

    card.addEventListener("click", () => this.toggleSubject(subject.id))

    return card
  }

  toggleSubject(subjectId) {
    if (this.selectedSubjects.has(subjectId)) {
      this.selectedSubjects.delete(subjectId)
    } else {
      this.selectedSubjects.add(subjectId)
    }

    this.updateSubjectCards()
    this.updateSelectedDisplay()
  }

  updateSubjectCards() {
    document.querySelectorAll(".subject-card").forEach((card) => {
      const subjectId = Number.parseInt(card.dataset.subjectId)
      if (this.selectedSubjects.has(subjectId)) {
        card.classList.add("selected")
      } else {
        card.classList.remove("selected")
      }
    })
  }

  updateSelectedDisplay() {
    const selectedList = document.getElementById("selected-list")
    selectedList.innerHTML = ""

    this.selectedSubjects.forEach((subjectId) => {
      const subject = this.subjects.find((s) => s.id === subjectId)
      if (subject) {
        const tag = document.createElement("div")
        tag.className = "selected-tag"
        tag.style.setProperty("--subject-color", subject.color_theme)
        tag.innerHTML = `
                    <i class="${subject.icon_class}"></i>
                    <span>${subject.subject_name}</span>
                    <button class="remove-btn" onclick="subjectSelection.toggleSubject(${subject.id})">
                        <i class="fas fa-times"></i>
                    </button>
                `
        selectedList.appendChild(tag)
      }
    })

    // Show/hide summary section
    const summary = document.getElementById("selected-summary")
    summary.style.display = this.selectedSubjects.size > 0 ? "block" : "none"
  }

  updatePageTitle() {
    const title = document.getElementById("page-title")
    if (this.userType === "teacher") {
      title.textContent = "What subjects do you teach?"
    } else {
      title.textContent = "What subjects are you studying?"
    }
  }

  setupEventListeners() {
    // Save selection
    document.getElementById("save-selection").addEventListener("click", () => {
      this.saveSelection()
    })

    // Add subject modal
    document.getElementById("add-subject-btn").addEventListener("click", () => {
      this.showAddSubjectModal()
    })

    document.getElementById("close-modal").addEventListener("click", () => {
      this.hideAddSubjectModal()
    })

    document.getElementById("cancel-add").addEventListener("click", () => {
      this.hideAddSubjectModal()
    })

    // Add subject form
    document.getElementById("add-subject-form").addEventListener("submit", (e) => {
      e.preventDefault()
      this.addCustomSubject()
    })

    // Color presets
    document.querySelectorAll(".color-preset").forEach((preset) => {
      const color = preset.dataset.color
      preset.style.backgroundColor = color
      preset.addEventListener("click", () => {
        document.getElementById("color-theme").value = color
      })
    })

    // Icon selection
    document.querySelectorAll(".icon-grid i").forEach((icon) => {
      icon.addEventListener("click", () => {
        document.querySelectorAll(".icon-grid i").forEach((i) => i.classList.remove("selected"))
        icon.classList.add("selected")
        document.getElementById("icon-class").value = icon.dataset.icon
      })
    })

    // Analytics button
    document.getElementById("analytics-btn").addEventListener("click", () => {
      this.showAnalytics()
    })

    // Modal backdrop click
    document.getElementById("add-subject-modal").addEventListener("click", (e) => {
      if (e.target.id === "add-subject-modal") {
        this.hideAddSubjectModal()
      }
    })
  }

  async saveSelection() {
    this.showLoading(true)

    try {
      const response = await fetch("save_user_subjects.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_type: this.userType,
          user_id: this.userId,
          subject_ids: Array.from(this.selectedSubjects),
        }),
      })

      const data = await response.json()

      if (data.success) {
        this.showNotification("Subject selection saved successfully!", "success")
      } else {
        throw new Error(data.message)
      }
    } catch (error) {
      console.error("Error saving selection:", error)
      this.showNotification("Error saving selection", "error")
    } finally {
      this.showLoading(false)
    }
  }

  showAddSubjectModal() {
    document.getElementById("add-subject-modal").classList.add("active")
    document.getElementById("subject-name").focus()
  }

  hideAddSubjectModal() {
    document.getElementById("add-subject-modal").classList.remove("active")
    document.getElementById("add-subject-form").reset()
    document.querySelectorAll(".icon-grid i").forEach((i) => i.classList.remove("selected"))
    document.querySelector(".icon-grid i").classList.add("selected")
  }

  async addCustomSubject() {
    const formData = new FormData(document.getElementById("add-subject-form"))
    formData.append("school_id", this.schoolId)

    try {
      const response = await fetch("add_custom_subject.php", {
        method: "POST",
        body: formData,
      })

      const data = await response.json()

      if (data.success) {
        this.showNotification("Custom subject added successfully!", "success")
        this.hideAddSubjectModal()
        await this.loadSubjects() // Reload subjects
      } else {
        throw new Error(data.message)
      }
    } catch (error) {
      console.error("Error adding custom subject:", error)
      this.showNotification("Error adding custom subject", "error")
    }
  }

  showAnalytics() {
    // Create analytics modal or redirect to analytics page
    const analyticsData = {
      totalSubjects: this.subjects.length,
      selectedSubjects: this.selectedSubjects.size,
      selectionPercentage: Math.round((this.selectedSubjects.size / this.subjects.length) * 100),
      userType: this.userType,
    }

    alert(
      `Analytics:\nTotal Subjects: ${analyticsData.totalSubjects}\nSelected: ${analyticsData.selectedSubjects}\nSelection Rate: ${analyticsData.selectionPercentage}%`,
    )
  }

  showLoading(show) {
    const overlay = document.getElementById("loading-overlay")
    if (show) {
      overlay.classList.add("active")
    } else {
      overlay.classList.remove("active")
    }
  }

  showNotification(message, type = "info") {
    // Create a simple notification system
    const notification = document.createElement("div")
    notification.className = `notification ${type}`
    notification.textContent = message
    notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 3000;
            animation: slideIn 0.3s ease;
            background: ${type === "success" ? "#10b981" : type === "error" ? "#ef4444" : "#667eea"};
        `

    document.body.appendChild(notification)

    setTimeout(() => {
      notification.remove()
    }, 3000)
  }
}

// Initialize the subject selection system
let subjectSelection
document.addEventListener("DOMContentLoaded", () => {
  subjectSelection = new SubjectSelection()
})

// Add CSS animation for notifications
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`
document.head.appendChild(style)
